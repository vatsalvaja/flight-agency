<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AuthController;

// Public Landing Page & Auth Actions
Route::get('/', [AuthController::class, 'showLanding'])->name('landing');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Admin Panel Routes
Route::middleware('admin.auth')->prefix('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    
    Route::resource('companies', CompanyController::class);
    
    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    
    Route::get('stations', function () {
        return view('admin.stations.index');
    })->name('stations.index');
    
    Route::get('locations', function () {
        return view('admin.locations.index');
    })->name('locations.index');
    
    Route::get('reports', function () {
        return view('admin.reports.index');
    })->name('reports.index');
});
