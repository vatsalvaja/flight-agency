<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\SettingsController;

Route::get('/', function () {
    return redirect('/admin');
});

Route::prefix('admin')->group(function () {
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
