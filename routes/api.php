<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| Public Authentication Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [
    AuthController::class,
    'login'
]);

Route::post('/verify-google-otp', [
    AuthController::class,
    'verifyGoogleOtp'
]);

Route::post('/verify-recovery-code', [
    AuthController::class,
    'verifyRecoveryCode'
]);

/*
|--------------------------------------------------------------------------
| Protected Sanctum Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [
        AuthController::class,
        'profile'
    ]);

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [
        AuthController::class,
        'logout'
    ]);

    /*
    |--------------------------------------------------------------------------
    | Recovery Codes
    |--------------------------------------------------------------------------
    */

    Route::post('/2fa/recovery-codes/regenerate', [
        AuthController::class,
        'regenerateRecoveryCodes'
    ]);

    /*
    |--------------------------------------------------------------------------
    | Security Statistics
    |--------------------------------------------------------------------------
    */

    Route::get('/security/statistics', [
        AuthController::class,
        'securityStatistics'
    ]);

    /*
    |--------------------------------------------------------------------------
    | Security Activity History
    |--------------------------------------------------------------------------
    */

    Route::get('/security/activities', [
        AuthController::class,
        'securityActivities'
    ]);

    /*
    |--------------------------------------------------------------------------
    | Sanctum Token Management
    |--------------------------------------------------------------------------
    */

    Route::get('/tokens', [
        AuthController::class,
        'tokens'
    ]);

    Route::post('/tokens', [
        AuthController::class,
        'createToken'
    ]);

    Route::delete('/tokens/{tokenId}', [
        AuthController::class,
        'revokeToken'
    ])->whereNumber('tokenId');

    Route::delete('/tokens/revoke-others', [
        AuthController::class,
        'revokeOtherTokens'
    ]);

    /*
    |--------------------------------------------------------------------------
    | 2FA Security Settings
    |--------------------------------------------------------------------------
    */

    Route::post('/security/2fa/new-authenticator', [
        AuthController::class,
        'startAuthenticatorSetup'
    ]);

    Route::post('/security/2fa/confirm-authenticator', [
        AuthController::class,
        'confirmAuthenticatorSetup'
    ]);

    Route::post('/security/2fa/disable', [
        AuthController::class,
        'disableTwoFactor'
    ]);
});