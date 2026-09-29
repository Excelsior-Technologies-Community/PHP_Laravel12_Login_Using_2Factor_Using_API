<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SecurityActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    // =========================================================
    // SECURITY ACTIVITY LOGGER
    // =========================================================

    private function logSecurityActivity(
        ?User $user,
        string $event,
        string $status,
        string $description,
        ?Request $request = null
    ): void {
        SecurityActivity::create([
            'user_id' => $user?->id,
            'event' => $event,
            'status' => $status,
            'description' => $description,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    // =========================================================
    // LOGIN - EMAIL + PASSWORD
    // =========================================================

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            $this->logSecurityActivity(
                $user,
                'password_login',
                'failed',
                'Failed password login attempt.',
                $request
            );

            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        Auth::login($user);

        $google2fa = new Google2FA();

        // First login / first 2FA setup
        if (!$user->google_2fa_secret) {
            $user->google_2fa_secret = $google2fa->generateSecretKey();
            $user->save();
        }

        // Generate recovery codes if missing
        if (empty($user->two_factor_recovery_codes)) {
            $user->generateRecoveryCodes();
        }

        $response = [
            'success' => true,
            'otp_required' => true,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->name,
            'google_2fa_enabled' => (bool) $user->google_2fa_enabled,
            'has_recovery_codes' => !empty($user->two_factor_recovery_codes),
            'remaining_recovery_codes' => $user->remainingRecoveryCodesCount(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Only expose QR/manual key during initial setup
        |--------------------------------------------------------------------------
        */

        if (!$user->google_2fa_enabled) {
            $qrCodeUrl = $google2fa->getQRCodeUrl(
                config('app.name', '2FA Portal'),
                $user->email,
                $user->google_2fa_secret
            );

            $response['qr_code'] = $qrCodeUrl;
            $response['manual_key'] = $user->google_2fa_secret;
        }

        return response()->json($response);
    }

    // =========================================================
    // VERIFY GOOGLE OTP
    // =========================================================

    public function verifyGoogleOtp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'otp' => 'required|string|size:6',
        ]);

        $user = User::find($request->user_id);

        if (!$user || !$user->google_2fa_secret) {
            return response()->json([
                'success' => false,
                'message' => '2FA is not setup for this user.',
            ], 400);
        }

        $google2fa = new Google2FA();

        $isValid = $google2fa->verifyKey(
            $user->google_2fa_secret,
            $request->otp,
            2
        );

        if (!$isValid) {
            $this->logSecurityActivity(
                $user,
                'google_otp_login',
                'failed',
                'Invalid Google Authenticator OTP.',
                $request
            );

            return response()->json([
                'success' => false,
                'message' => 'Invalid Google OTP code. Please check your Google Authenticator app.',
            ], 401);
        }

        // Enable 2FA after first successful OTP verification
        if (!$user->google_2fa_enabled) {
            $user->google_2fa_enabled = true;
            $user->save();
        }

        if (empty($user->two_factor_recovery_codes)) {
            $user->generateRecoveryCodes();
        }

        $token = $user->createToken('api-token')->plainTextToken;

        $this->logSecurityActivity(
            $user,
            'google_otp_login',
            'success',
            'Successful login using Google Authenticator OTP.',
            $request
        );

        $this->logSecurityActivity(
            $user,
            'api_token_created',
            'success',
            'API token created after successful Google OTP login.',
            $request
        );

        return response()->json([
            'success' => true,
            'message' => '2FA Login successful via Google Authenticator.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'google_2fa_enabled' => (bool) $user->google_2fa_enabled,
                'recovery_codes' => $user->getRecoveryCodesList(),
            ],
        ]);
    }

    // =========================================================
    // VERIFY RECOVERY CODE
    // =========================================================

    public function verifyRecoveryCode(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'recovery_code' => 'required|string',
        ]);

        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $isValid = $user->verifyAndConsumeRecoveryCode(
            $request->recovery_code
        );

        if (!$isValid) {
            $this->logSecurityActivity(
                $user,
                'recovery_code_login',
                'failed',
                'Invalid or already used recovery code.',
                $request
            );

            return response()->json([
                'success' => false,
                'message' => 'Invalid or already used emergency recovery code.',
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        $this->logSecurityActivity(
            $user,
            'recovery_code_login',
            'success',
            'Successful login using an emergency recovery code.',
            $request
        );

        $this->logSecurityActivity(
            $user,
            'api_token_created',
            'success',
            'API token created after recovery-code login.',
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Login successful via Emergency Backup Recovery Code.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'google_2fa_enabled' => (bool) $user->google_2fa_enabled,
                'recovery_codes' => $user->getRecoveryCodesList(),
            ],
        ]);
    }

    // =========================================================
    // REGENERATE RECOVERY CODES
    // =========================================================

    public function regenerateRecoveryCodes(Request $request)
    {
        $user = $request->user();

        if (!$user && $request->has('user_id')) {
            $user = User::find($request->user_id);
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized or user not found.',
            ], 401);
        }

        $newCodes = $user->generateRecoveryCodes();

        $this->logSecurityActivity(
            $user,
            'recovery_codes_regenerated',
            'success',
            'Emergency recovery codes were regenerated.',
            $request
        );

        return response()->json([
            'success' => true,
            'message' => '8 fresh emergency recovery codes generated successfully.',
            'recovery_codes' => $user->getRecoveryCodesList(),
            'plain_codes' => $newCodes,
        ]);
    }

    // =========================================================
    // PROFILE
    // =========================================================

    public function profile(Request $request)
    {
        $user = $request->user();

        $codes = $user->getRecoveryCodesList();

        $usedCount = count(
            array_filter($codes, fn ($c) => !empty($c['used_at']))
        );

        $remainingCount = count(
            array_filter($codes, fn ($c) => empty($c['used_at']))
        );

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'google_2fa_enabled' => (bool) $user->google_2fa_enabled,

                // Do NOT expose the permanent secret here.
                'recovery_stats' => [
                    'total' => count($codes),
                    'used' => $usedCount,
                    'remaining' => $remainingCount,
                ],

                'recovery_codes' => $codes,
            ],
        ]);
    }

    // =========================================================
    // SECURITY STATISTICS
    // =========================================================

    public function securityStatistics(Request $request)
    {
        $user = $request->user();

        $query = SecurityActivity::where('user_id', $user->id);

        return response()->json([
            'success' => true,

            'statistics' => [
                'total_events' => (clone $query)->count(),

                'successful_logins' => (clone $query)
                    ->whereIn('event', [
                        'google_otp_login',
                        'recovery_code_login',
                    ])
                    ->where('status', 'success')
                    ->count(),

                'failed_logins' => (clone $query)
                    ->where('status', 'failed')
                    ->count(),

                'otp_verifications' => (clone $query)
                    ->where('event', 'google_otp_login')
                    ->count(),

                'recovery_code_logins' => (clone $query)
                    ->where('event', 'recovery_code_login')
                    ->where('status', 'success')
                    ->count(),

                'today_events' => (clone $query)
                    ->whereDate('created_at', today())
                    ->count(),

                'successful_events' => (clone $query)
                    ->where('status', 'success')
                    ->count(),

                'failed_events' => (clone $query)
                    ->where('status', 'failed')
                    ->count(),
            ],
        ]);
    }

    // =========================================================
    // SECURITY ACTIVITY HISTORY
    // =========================================================

    public function securityActivities(Request $request)
    {
        $user = $request->user();

        $query = SecurityActivity::where('user_id', $user->id);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('event', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $activities = $query
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'activities' => $activities,
        ]);
    }

    // =========================================================
    // TOKEN LIST
    // =========================================================

    public function tokens(Request $request)
    {
        $user = $request->user();

        $currentTokenId = optional(
            $user->currentAccessToken()
        )->id;

        $tokens = $user->tokens()
            ->latest()
            ->get()
            ->map(function ($token) use ($currentTokenId) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'created_at' => $token->created_at?->format('Y-m-d H:i:s'),
                    'last_used_at' => $token->last_used_at?->format('Y-m-d H:i:s'),
                    'expires_at' => $token->expires_at?->format('Y-m-d H:i:s'),
                    'is_current' => $token->id === $currentTokenId,
                ];
            });

        return response()->json([
            'success' => true,
            'tokens' => $tokens,
        ]);
    }

    // =========================================================
    // CREATE NAMED TOKEN
    // =========================================================

    public function createToken(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $user = $request->user();

        $token = $user->createToken(
            trim($request->name)
        );

        $this->logSecurityActivity(
            $user,
            'api_token_created',
            'success',
            "Created API token: {$request->name}",
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'API token created successfully.',
            'token' => $token->plainTextToken,
            'token_id' => $token->accessToken->id,
            'name' => $request->name,
        ]);
    }

    // =========================================================
    // REVOKE ONE TOKEN
    // =========================================================

    public function revokeToken(Request $request, int $tokenId)
    {
        $user = $request->user();

        $token = $user->tokens()
            ->where('id', $tokenId)
            ->first();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token not found.',
            ], 404);
        }

        $tokenName = $token->name;

        $token->delete();

        $this->logSecurityActivity(
            $user,
            'api_token_revoked',
            'success',
            "Revoked API token: {$tokenName}",
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'API token revoked successfully.',
        ]);
    }

    // =========================================================
    // REVOKE ALL OTHER TOKENS
    // =========================================================

    public function revokeOtherTokens(Request $request)
    {
        $user = $request->user();

        $currentToken = $user->currentAccessToken();

        if (!$currentToken) {
            return response()->json([
                'success' => false,
                'message' => 'Current API token could not be identified.',
            ], 400);
        }

        $deleted = $user->tokens()
            ->where('id', '!=', $currentToken->id)
            ->delete();

        $this->logSecurityActivity(
            $user,
            'api_tokens_revoked_all',
            'success',
            "{$deleted} other API token(s) revoked.",
            $request
        );

        return response()->json([
            'success' => true,
            'message' => "{$deleted} other API token(s) revoked successfully.",
            'revoked_count' => $deleted,
        ]);
    }

    // =========================================================
    // START NEW AUTHENTICATOR SETUP
    // =========================================================

    public function startAuthenticatorSetup(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'password' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Step-up authentication
        |--------------------------------------------------------------------------
        */

        if (!Hash::check($request->password, $user->password)) {
            $this->logSecurityActivity(
                $user,
                'security_settings',
                'failed',
                'Failed password verification while starting authenticator setup.',
                $request
            );

            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        if (!$user->google_2fa_secret) {
            return response()->json([
                'success' => false,
                'message' => 'Current authenticator is not configured.',
            ], 422);
        }

        $google2fa = new Google2FA();

        if (!$google2fa->verifyKey(
            $user->google_2fa_secret,
            $request->otp,
            2
        )) {
            $this->logSecurityActivity(
                $user,
                'security_settings',
                'failed',
                'Failed OTP verification while changing authenticator.',
                $request
            );

            return response()->json([
                'success' => false,
                'message' => 'Current Google Authenticator OTP is invalid.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Generate new secret
        |--------------------------------------------------------------------------
        */

        $newSecret = $google2fa->generateSecretKey();

        $user->google_2fa_secret = $newSecret;
        $user->google_2fa_enabled = false;
        $user->save();

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name', '2FA Portal'),
            $user->email,
            $newSecret
        );

        $this->logSecurityActivity(
            $user,
            '2fa_secret_regenerated',
            'success',
            'A new Google Authenticator secret was generated.',
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'New authenticator secret generated. Verify the new OTP to enable 2FA.',
            'qr_code' => $qrCodeUrl,
            'manual_key' => $newSecret,
        ]);
    }

    // =========================================================
    // CONFIRM NEW AUTHENTICATOR
    // =========================================================

    public function confirmAuthenticatorSetup(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $google2fa = new Google2FA();

        if (!$user->google_2fa_secret) {
            return response()->json([
                'success' => false,
                'message' => 'No authenticator secret exists.',
            ], 422);
        }

        if (!$google2fa->verifyKey(
            $user->google_2fa_secret,
            $request->otp,
            2
        )) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP. Please scan the new QR code and try again.',
            ], 422);
        }

        $user->google_2fa_enabled = true;

        if (empty($user->two_factor_recovery_codes)) {
            $user->generateRecoveryCodes();
        } else {
            $user->save();
        }

        $this->logSecurityActivity(
            $user,
            '2fa_enabled',
            'success',
            'New Google Authenticator setup was successfully confirmed.',
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'New authenticator successfully enabled.',
        ]);
    }

    // =========================================================
    // DISABLE 2FA
    // =========================================================

    public function disableTwoFactor(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'password' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        if (!Hash::check($request->password, $user->password)) {
            $this->logSecurityActivity(
                $user,
                '2fa_disabled',
                'failed',
                'Failed password verification while disabling 2FA.',
                $request
            );

            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        if (!$user->google_2fa_secret) {
            return response()->json([
                'success' => false,
                'message' => '2FA is not configured.',
            ], 422);
        }

        $google2fa = new Google2FA();

        if (!$google2fa->verifyKey(
            $user->google_2fa_secret,
            $request->otp,
            2
        )) {
            $this->logSecurityActivity(
                $user,
                '2fa_disabled',
                'failed',
                'Failed OTP verification while disabling 2FA.',
                $request
            );

            return response()->json([
                'success' => false,
                'message' => 'Current Google Authenticator OTP is invalid.',
            ], 422);
        }

        $user->google_2fa_enabled = false;
        $user->save();

        $this->logSecurityActivity(
            $user,
            '2fa_disabled',
            'success',
            'Google Authenticator 2FA was disabled.',
            $request
        );

        return response()->json([
            'success' => true,
            'message' => '2FA has been disabled successfully.',
        ]);
    }

    // =========================================================
    // LOGOUT
    // =========================================================

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $token = $user->currentAccessToken();

            $this->logSecurityActivity(
                $user,
                'logout',
                'success',
                'User logged out successfully.',
                $request
            );

            if ($token) {
                $token->delete();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}