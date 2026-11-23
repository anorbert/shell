<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OperationsController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', 'verified'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/clients', [ClientController::class, 'index'])->name('clients');
    Route::get('/operations', OperationsController::class)->name('operations');
});
