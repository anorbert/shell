<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ClientModuleController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/markets', [ClientModuleController::class, 'markets'])->name('markets.index');
    Route::get('/trading', [ClientModuleController::class, 'trading'])->name('trading.index');
    Route::get('/portfolio', [ClientModuleController::class, 'portfolio'])->name('portfolio.index');
    Route::get('/activity', [ClientModuleController::class, 'activity'])->name('activity.index');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/clients', [ClientController::class, 'index'])->name('clients');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
