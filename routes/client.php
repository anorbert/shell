<?php
use App\Http\Controllers\Client\ActivityController;
use App\Http\Controllers\Client\MarketController;
use App\Http\Controllers\Client\NotificationController;
use App\Http\Controllers\Client\PaperTradingController;
use App\Http\Controllers\Client\PortfolioController;
use Illuminate\Support\Facades\Route;
Route::middleware(['auth','client','verified'])->group(function(){
 Route::get('/markets',[MarketController::class,'index'])->name('markets.index');
 Route::get('/markets/data',[MarketController::class,'data'])->name('markets.data');
 Route::post('/markets/{symbol}/favorite',[MarketController::class,'toggleFavorite'])->middleware('throttle:60,1')->name('markets.favorite');
 Route::get('/trading',[PaperTradingController::class,'index'])->name('trading.index');
 Route::post('/trading/paper',[PaperTradingController::class,'store'])->middleware('throttle:20,1')->name('trading.store');
 Route::get('/portfolio',PortfolioController::class)->name('portfolio.index');
 Route::get('/activity',ActivityController::class)->name('activity.index');
 Route::get('/activity/export',[ActivityController::class,'export'])->name('activity.export');
 Route::get('/notifications',[NotificationController::class,'index'])->name('notifications.index');
 Route::post('/notifications/{notification}/read',[NotificationController::class,'markRead'])->middleware('throttle:60,1')->name('notifications.read');
});