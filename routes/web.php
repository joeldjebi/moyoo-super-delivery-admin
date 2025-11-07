<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Route de connexion
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
Route::post('/login', [AuthController::class, 'loginUser'])->name('auth.login.post');

// Routes Platform Admin (chargées depuis un fichier séparé)
require __DIR__.'/platform-admin.php';
