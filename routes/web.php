<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CredentialController; 
use App\Http\Controllers\StorageController; 
use App\Http\Controllers\ServiceController; 

Route::get('/', [AuthController::class, 'showLandingPage'])->name('welcome');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    
    Route::get('/dashboard', [CredentialController::class, 'revealCredentialsPage'])->name('dashboard');
    Route::post('/dashboard/reveal', [CredentialController::class, 'showCredentials'])->name('dashboard.reveal');
    
    // Paket Sewa
    Route::get('/services', [ServiceController::class, 'index'])->name('services');
    Route::post('/services/upgrade', [ServiceController::class, 'upgrade'])->name('services.upgrade'); 
    
    // Penyimpanan
    Route::get('/storage', [StorageController::class, 'index'])->name('storage');
    Route::post('/storage/upload', [StorageController::class, 'upload'])->name('storage.upload'); 
    Route::get('/storage/download/{id}', [StorageController::class, 'download'])->name('storage.download');
    Route::post('/storage/log-success', [StorageController::class, 'logSuccess'])->name('storage.log_success');
    Route::delete('/storage/{id}', [StorageController::class, 'destroy'])->name('storage.destroy');
    Route::get('/logs', [StorageController::class, 'activityLogs'])->name('storage.logs');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});