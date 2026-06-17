<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;

// Public Landing Page & Auth Actions
Route::get('/', [AuthController::class, 'showLanding'])->name('landing');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Admin Panel Routes
Route::middleware('admin.auth')->prefix('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    
    Route::resource('companies', CompanyController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    
    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    
    Route::get('profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::get('account-settings', [\App\Http\Controllers\ProfileController::class, 'accountSettings'])->name('account-settings.edit');
    Route::put('change-password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('change-password.update');
    
    Route::resource('stations', \App\Http\Controllers\StationController::class);

    
    Route::get('reports', function () {
        return view('admin.reports.index');
    })->name('reports.index');

    Route::resource('assign-luggage', \App\Http\Controllers\AssignLuggageController::class);

    Route::get('driver-activities', [\App\Http\Controllers\DriverActivitiesController::class, 'index'])->name('driver-activities.index');

    Route::get('assignable-orders', [\App\Http\Controllers\AssignableOrdersController::class, 'index'])->name('assignable-orders.index');
    Route::get('assignable-orders/{id}', [\App\Http\Controllers\AssignableOrdersController::class, 'show'])->name('assignable-orders.show');
    Route::post('assignable-orders/{id}/pickup', [\App\Http\Controllers\AssignableOrdersController::class, 'pickup'])->name('assignable-orders.pickup');
    Route::post('assignable-orders/{id}/deliver', [\App\Http\Controllers\AssignableOrdersController::class, 'deliver'])->name('assignable-orders.deliver');
});
