<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    // =========================
    // LOGIN (EMAIL + PASSWORD)
    // =========================
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email','password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();
        $google2fa = new Google2FA();

        // First time login → generate secret
        if (!$user->google_2fa_secret) {
            $user->google_2fa_secret = $google2fa->generateSecretKey();
            $user->save();
        }

        // Check if user has recovery codes, otherwise generate them
        if (empty($user->two_factor_recovery_codes)) {
            $user->generateRecoveryCodes();
        }

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name', '2FA Portal'),
            $user->email,
            $user->google_2fa_secret
        );

        $recoveryCodesList = $user->getRecoveryCodesList();
        $remainingRecoveryCodes = count(array_filter($recoveryCodesList, fn($c) => empty($c['used_at'])));

        return response()->json([
            'success' => true,
            'otp_required' => true,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->name,
            'google_2fa_enabled' => (bool)$user->google_2fa_enabled,
            'qr_code' => $qrCodeUrl,
            'manual_key' => $user->google_2fa_secret,
            'has_recovery_codes' => !empty($recoveryCodesList),
            'remaining_recovery_codes' => $remainingRecoveryCodes,
        ]);
    }

    // =========================
    // VERIFY GOOGLE OTP
    // =========================
    public function verifyGoogleOtp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'otp' => 'required|string'
        ]);

        $user = User::find($request->user_id);

        if (!$user || !$user->google_2fa_secret) {
            return response()->json([
                'success' => false,
                'message' => '2FA not setup for this user'
            ], 400);
        }

        $google2fa = new Google2FA();

        $isValid = $google2fa->verifyKey(
            $user->google_2fa_secret,
            $request->otp,
            2 // allow small time difference
        );

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Google OTP code. Please check your Google Authenticator app.'
            ], 401);
        }

        // Enable 2FA permanently if not enabled
        if (!$user->google_2fa_enabled) {
            $user->google_2fa_enabled = true;
            $user->save();
        }

        // Ensure recovery codes exist
        if (empty($user->two_factor_recovery_codes)) {
            $user->generateRecoveryCodes();
        }

        // Generate API Token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => '2FA Login successful via Google Authenticator',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'google_2fa_enabled' => (bool)$user->google_2fa_enabled,
                'recovery_codes' => $user->getRecoveryCodesList(),
            ]
        ]);
    }

    // ============================================
    // VERIFY EMERGENCY BACKUP RECOVERY CODE
    // ============================================
    public function verifyRecoveryCode(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'recovery_code' => 'required|string'
        ]);

        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $isValid = $user->verifyAndConsumeRecoveryCode($request->recovery_code);

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or already used emergency recovery code.'
            ], 401);
        }

        // Generate API Token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful via Emergency Backup Recovery Code',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'google_2fa_enabled' => (bool)$user->google_2fa_enabled,
                'recovery_codes' => $user->getRecoveryCodesList(),
            ]
        ]);
    }

    // ============================================
    // REGENERATE EMERGENCY BACKUP RECOVERY CODES
    // ============================================
    public function regenerateRecoveryCodes(Request $request)
    {
        $user = $request->user();

        // Support passing user_id if not token authenticated
        if (!$user && $request->has('user_id')) {
            $user = User::find($request->user_id);
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized or user not found'
            ], 401);
        }

        $newCodes = $user->generateRecoveryCodes();

        return response()->json([
            'success' => true,
            'message' => '8 fresh emergency recovery codes generated successfully',
            'recovery_codes' => $user->getRecoveryCodesList(),
            'plain_codes' => $newCodes,
        ]);
    }

    // ============================================
    // GET PROFILE WITH 2FA & RECOVERY STATUS
    // ============================================
    public function profile(Request $request)
    {
        $user = $request->user();
        $codes = $user->getRecoveryCodesList();
        $usedCount = count(array_filter($codes, fn($c) => !empty($c['used_at'])));
        $remainingCount = count(array_filter($codes, fn($c) => empty($c['used_at'])));

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'google_2fa_enabled' => (bool)$user->google_2fa_enabled,
                'manual_key' => $user->google_2fa_secret,
                'recovery_stats' => [
                    'total' => count($codes),
                    'used' => $usedCount,
                    'remaining' => $remainingCount,
                ],
                'recovery_codes' => $codes,
            ]
        ]);
    }

    // ============================================
    // LOGOUT
    // ============================================
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
