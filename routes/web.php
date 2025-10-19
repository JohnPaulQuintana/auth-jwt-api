<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});




Route::get('/reset-password', function (Request $request) {
    $token = $request->token;
    $email = $request->email;

    return view('reset-password-form', compact('token', 'email'));
});

