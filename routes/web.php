<?php
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\PaperTradingController;
use App\Http\Controllers\PortfolioController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::middleware('auth')->get('/dashboard', DashboardController::class)->name('dashboard');

Route::middleware(['auth', 'client'])->group(function () {
    Route::get('/markets', [MarketController::class, 'index'])->name('markets.index');
    Route::get('/markets/data', [MarketController::class, 'data'])->name('markets.data');
    Route::get('/trading', [PaperTradingController::class, 'index'])->name('trading.index');
    Route::post('/trading/paper', [PaperTradingController::class, 'store'])->middleware('throttle:20,1')->name('trading.store');
    Route::get('/portfolio', PortfolioController::class)->name('portfolio.index');
    Route::get('/activity', ActivityController::class)->name('activity.index');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/clients', [ClientController::class, 'index'])->name('clients');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
