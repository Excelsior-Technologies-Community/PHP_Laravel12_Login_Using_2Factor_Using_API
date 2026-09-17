<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request; // 
use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-google-otp', [AuthController::class, 'verifyGoogleOtp']);
Route::post('/verify-recovery-code', [AuthController::class, 'verifyRecoveryCode']);
Route::post('/2fa/recovery-codes/regenerate', [AuthController::class, 'regenerateRecoveryCodes']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

