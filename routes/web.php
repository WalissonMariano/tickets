<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TaskController;
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

Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');

Route::get('/account', fn () => view('account.index-account'))->name('account.index');

Route::prefix('register')->name('register.')->group(function () {
    Route::get('/groups', fn () => view('register.groups.index-groups'))->name('groups.index');
    Route::get('/users', fn () => view('register.users.index-users'))->name('users.index');
    Route::get('/projects', fn () => view('register.projects.index-projects'))->name('projects.index');
});

/*
Route::middleware('auth')->group(function () {
    Route::get('/menu', function () {
        return view('layouts.menu');
    })->name('menu');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
*/
