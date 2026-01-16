<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request; // 
use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-google-otp', [AuthController::class, 'verifyGoogleOtp']);

Route::middleware('auth:sanctum')->get('/profile', function (Request $request) {
    return $request->user();
});
