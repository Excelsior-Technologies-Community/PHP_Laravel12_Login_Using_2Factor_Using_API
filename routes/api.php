<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| PUBLIC AUTHENTICATION
|--------------------------------------------------------------------------
*/

Route::post(
    '/login',
    [AuthController::class, 'login']
);

Route::post(
    '/verify-google-otp',
    [AuthController::class, 'verifyGoogleOtp']
);

Route::post(
    '/verify-recovery-code',
    [AuthController::class, 'verifyRecoveryCode']
);


/*
|--------------------------------------------------------------------------
| PROTECTED SANCTUM ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/profile',
        [AuthController::class, 'profile']
    );

    // NEW 1
    Route::put(
        '/profile',
        [AuthController::class, 'updateProfile']
    );


    /*
    |--------------------------------------------------------------------------
    | Password
    |--------------------------------------------------------------------------
    */

    // NEW 2
    Route::put(
        '/password',
        [AuthController::class, 'changePassword']
    );


    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/logout',
        [AuthController::class, 'logout']
    );

    // NEW 3 / 6
    Route::post(
        '/logout-all-devices',
        [AuthController::class, 'logoutAllDevices']
    );


    /*
    |--------------------------------------------------------------------------
    | Recovery Codes
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/2fa/recovery-codes/regenerate',
        [AuthController::class, 'regenerateRecoveryCodes']
    );


    /*
    |--------------------------------------------------------------------------
    | Security Statistics
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/security/statistics',
        [AuthController::class, 'securityStatistics']
    );


    // NEW 8
    Route::get(
        '/security/summary',
        [AuthController::class, 'activitySummary']
    );


    /*
    |--------------------------------------------------------------------------
    | Security Activities
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/security/activities',
        [AuthController::class, 'securityActivities']
    );


    // NEW 9
    Route::get(
        '/security/activities/export',
        [AuthController::class, 'exportActivities']
    );


    /*
    |--------------------------------------------------------------------------
    | Token Management
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/tokens',
        [AuthController::class, 'tokens']
    );

    Route::post(
        '/tokens',
        [AuthController::class, 'createToken']
    );

    Route::delete(
        '/tokens/{tokenId}',
        [AuthController::class, 'revokeToken']
    )->whereNumber('tokenId');

    Route::delete(
        '/tokens/revoke-others',
        [AuthController::class, 'revokeOtherTokens']
    );


    /*
    |--------------------------------------------------------------------------
    | NEW 5 - Current Session
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/session/current',
        [AuthController::class, 'currentSession']
    );


    /*
    |--------------------------------------------------------------------------
    | 2FA Security Settings
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/security/2fa/new-authenticator',
        [AuthController::class, 'startAuthenticatorSetup']
    );

    Route::post(
        '/security/2fa/confirm-authenticator',
        [AuthController::class, 'confirmAuthenticatorSetup']
    );

    Route::post(
        '/security/2fa/disable',
        [AuthController::class, 'disableTwoFactor']
    );
});