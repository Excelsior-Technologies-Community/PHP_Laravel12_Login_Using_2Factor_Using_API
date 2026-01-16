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

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->google_2fa_secret
        );

        return response()->json([
            'success' => true,
            'otp_required' => true,
            'user_id' => $user->id,
            'qr_code' => $qrCodeUrl,
            'manual_key' => $user->google_2fa_secret
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
                'message' => '2FA not setup'
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
                'message' => 'Invalid OTP'
            ], 401);
        }

        // Enable 2FA permanently
        $user->google_2fa_enabled = true;
        $user->save();

        // Generate API Token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token
        ]);
    }
}
