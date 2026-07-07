<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

//Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
///});

Route::get('/menu', function () {
        return view('layouts.menu');
    })->name('menu');

Route::get('/home', function () {
    return view('home.home');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard.dashboard');
})->name('dashboard');

/*
Route::middleware('auth')->group(function () {
    Route::get('/menu', function () {
        return view('layouts.menu');
    })->name('menu');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
*/
