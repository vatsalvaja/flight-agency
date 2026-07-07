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
    
    Route::get('companies/list', [CompanyController::class, 'list'])->name('companies.list');
    Route::get('companies/{company}/data', [CompanyController::class, 'getDataById'])->name('companies.data');
    Route::post('companies/save', [CompanyController::class, 'save'])->name('companies.save');
    Route::resource('companies', CompanyController::class);
    Route::get('roles/list', [RoleController::class, 'list'])->name('roles.list');
    Route::get('roles/{role}/data', [RoleController::class, 'getDataById'])->name('roles.data');
    Route::post('roles/save', [RoleController::class, 'save'])->name('roles.save');
    Route::resource('roles', RoleController::class);

    Route::get('users/list', [UserController::class, 'list'])->name('users.list');
    Route::get('users/{user}/data', [UserController::class, 'getDataById'])->name('users.data');
    Route::post('users/save', [UserController::class, 'save'])->name('users.save');
    Route::resource('users', UserController::class);
    
    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('settings/smtp', [SettingsController::class, 'smtpEdit'])->name('settings.smtp.edit');
    Route::put('settings/smtp', [SettingsController::class, 'smtpUpdate'])->name('settings.smtp.update');
    Route::delete('settings/logo', [SettingsController::class, 'removeLogo'])->name('settings.logo.destroy');
    Route::delete('settings/favicon', [SettingsController::class, 'removeFavicon'])->name('settings.favicon.destroy');
    
    Route::get('profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::get('account-settings', [\App\Http\Controllers\ProfileController::class, 'accountSettings'])->name('account-settings.edit');
    Route::put('change-password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('change-password.update');
    
    Route::get('stations/list', [\App\Http\Controllers\StationController::class, 'list'])->name('stations.list');
    Route::get('stations/{station}/data', [\App\Http\Controllers\StationController::class, 'getDataById'])->name('stations.data');
    Route::post('stations/save', [\App\Http\Controllers\StationController::class, 'save'])->name('stations.save');
    Route::resource('stations', \App\Http\Controllers\StationController::class);

    
    Route::get('reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');

    Route::get('assign-luggage/list', [\App\Http\Controllers\AssignLuggageController::class, 'list'])->name('assign-luggage.list');
    Route::get('assign-luggage/{id}/data', [\App\Http\Controllers\AssignLuggageController::class, 'getDataById'])->name('assign-luggage.data');
    Route::post('assign-luggage/save', [\App\Http\Controllers\AssignLuggageController::class, 'save'])->name('assign-luggage.save');
    Route::resource('assign-luggage', \App\Http\Controllers\AssignLuggageController::class);

    Route::get('driver-activities', [\App\Http\Controllers\DriverActivitiesController::class, 'index'])->name('driver-activities.index');

    Route::get('assignable-orders', [\App\Http\Controllers\AssignableOrdersController::class, 'index'])->name('assignable-orders.index');
    Route::get('assignable-orders/{id}', [\App\Http\Controllers\AssignableOrdersController::class, 'show'])->name('assignable-orders.show');
    Route::post('assignable-orders/{id}/pickup', [\App\Http\Controllers\AssignableOrdersController::class, 'pickup'])->name('assignable-orders.pickup');
    Route::post('assignable-orders/{id}/deliver', [\App\Http\Controllers\AssignableOrdersController::class, 'deliver'])->name('assignable-orders.deliver');

    // Real-Time Location Tracking
    Route::post('assignable-orders/{id}/location', [\App\Http\Controllers\DriverLocationController::class, 'updateLocation'])->name('assignable-orders.location');
    Route::get('assign-luggage/{id}/tracking-data', [\App\Http\Controllers\DriverLocationController::class, 'getTrackingData'])->name('assign-luggage.tracking-data');
});
