# PHP_Laravel12_Login_Using_2Factor_Using_API

<p align="center">
  <a href="https://laravel.com">
    <img src="https://img.shields.io/badge/Laravel-12-red" alt="Laravel Version">
  </a>
  <a href="https://www.php.net/">
    <img src="https://img.shields.io/badge/PHP-8.2-blue" alt="PHP Version">
  </a>
  <a href="#">
    <img src="https://img.shields.io/badge/Auth-Google%202FA-success" alt="Google 2FA">
  </a>
  <a href="#">
    <img src="https://img.shields.io/badge/API-Sanctum-orange" alt="Laravel Sanctum">
  </a>
  <a href="#">
    <img src="https://img.shields.io/badge/Status-Working-brightgreen" alt="Project Status">
  </a>
</p>

---

##  Overview

This project demonstrates how to implement **Two-Factor Authentication (2FA)** using **Google Authenticator** in a **Laravel API-based authentication system**.

The flow is:

1. User logs in with email & password
2. Server generates a Google Authenticator secret
3. User verifies OTP from Google Authenticator app
4. API token is issued using Laravel Sanctum
5. Protected APIs can be accessed using the token

---

##  Features

* Laravel 12 API authentication
* Google Authenticator (TOTP) based 2FA
* Secure OTP verification
* Laravel Sanctum token-based authentication
* Protected API routes
* Production-ready structure

---

##  Folder Structure (Important Files)

```
twofa-api/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/
│   │           └── AuthController.php   
│   └── Models/
│       └── User.php                     
│
├── bootstrap/
│   └── app.php                     
│
├── config/
│   ├── sanctum.php                    
│   └── google2fa.php                 
│
├── database/
│   └── migrations/
│       └── add_google_2fa_to_users_table.php
│
├── routes/
│   └── api.php                  
│
├── .env                              
├── composer.json
├── artisan
└── README.md

.env
```

---

## STEP 1: Create New Laravel Project

```bash
composer create-project laravel/laravel twofa-api

```

---

## STEP 2: Environment & Database Setup

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=twofa_api
DB_USERNAME=root
DB_PASSWORD=
```

Create database:

```sql
CREATE DATABASE twofa_api;
```

Run default migrations:

```bash
php artisan migrate
```

---

## STEP 3: Install Sanctum (API Tokens)

```bash
composer require laravel/sanctum
```

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

```bash
php artisan migrate
```

---

## STEP 4: Install Google Authenticator (TOTP)

```bash
composer require pragmarx/google2fa-laravel
```

```bash
php artisan vendor:publish --provider="PragmaRX\Google2FALaravel\ServiceProvider"
```

---

## STEP 5: Add 2FA Columns to Users Table

Create migration:

```bash
php artisan make:migration add_google_2fa_to_users_table
```

Migration code:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('google_2fa_enabled')->default(false);
            $table->string('google_2fa_secret')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // rollback if needed
        });
    }
};
```

Run migration:

```bash
php artisan migrate
```

---

## STEP 6: User Model

`app/Models/User.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_2fa_enabled',
        'google_2fa_secret',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_2fa_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'google_2fa_enabled' => 'boolean',
        ];
    }
}
```

---

## STEP 7: Create Auth Controller

```bash
php artisan make:controller Api/AuthController
```

---

## STEP 8: AuthController

`app/Http/Controllers/Api/AuthController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    // LOGIN (EMAIL + PASSWORD)
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

    // VERIFY GOOGLE OTP
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
            2
        );

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP'
            ], 401);
        }

        $user->google_2fa_enabled = true;
        $user->save();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token
        ]);
    }
}
```

---

## STEP 9: Define API Routes

`routes/api.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-google-otp', [AuthController::class, 'verifyGoogleOtp']);

Route::middleware('auth:sanctum')->get('/profile', function (Request $request) {
    return $request->user();
});
```

---

## STEP 10: Create Test User

```bash
php artisan tinker
```

```php
User::create([
 'name' => 'Test User',
 'email' => 'test@gmail.com',
 'password' => bcrypt('password123')
]);
```

---

## STEP 11: Google Authenticator Setup (IMPORTANT)

Use **Google Authenticator MOBILE APP ONLY**

* Add account
* Account name: **Laravel Test**
* Key: **YOUR_LOGIN_MANUAL_KEY** (Example: `HN6HARKSRWQBFMBE`)
* Type: **Time-based**

### Time Sync (Must)

**Phone**:

* Date & Time → Automatic ON
* Time zone → Automatic ON

**PC**:

* Automatic time ON

❌ Do NOT use Google Account website
❌ Do NOT scan random QR codes

---

## STEP 12: API Testing (Postman)

### Login

```
POST http://localhost:8000/api/login
```

```json
{
  "email": "test@gmail.com",
  "password": "password123"
}
```
<img width="1308" height="753" alt="Screenshot 2026-01-16 132123" src="https://github.com/user-attachments/assets/cb1be712-e512-4b2c-bea0-4058e7687388" />


### Verify OTP

```
POST http://localhost:8000/api/verify-google-otp
```

```json
{
  "user_id": 2,
  "otp": "376972"
}
```
<img width="1252" height="662" alt="Screenshot 2026-01-16 130036" src="https://github.com/user-attachments/assets/4a062389-80d6-47bc-bede-0585239c5d85" />


### Access Protected API

```
GET http://localhost:8000/api/profile
```

Headers:

```
Authorization: Bearer YOUR_TOKEN
Accept: application/json
```
<img width="1279" height="695" alt="Screenshot 2026-01-16 130136" src="https://github.com/user-attachments/assets/fa085ad9-0f0d-45d7-b5c8-f9a271f2ba66" />

---

##  Final Result

* Secure login with Google Authenticator 2FA
* Token-based authentication using Sanctum
* Protected APIs accessible only after OTP verification

