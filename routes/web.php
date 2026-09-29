<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('twofa-dashboard');
});

Route::get('/dashboard', function () {
    return view('twofa-dashboard');
});