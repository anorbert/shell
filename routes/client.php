<?php

use App\Http\Controllers\Client\ActivityController;
use App\Http\Controllers\Client\DemoWalletController;
use App\Http\Controllers\Client\MarketController;
use App\Http\Controllers\Client\NotificationController;
use App\Http\Controllers\Client\PaperConversionController;
use App\Http\Controllers\Client\PaperTradingController;
use App\Http\Controllers\Client\Settings\SecurityController;
use App\Http\Controllers\Client\SupportController;
use App\Http\Controllers\Client\PortfolioController;
use App\Http\Controllers\Client\ReferralController;
use App\Http\Controllers\Client\StatementController;
use App\Http\Controllers\Client\WalletRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/r/{code}', [ReferralController::class, 'capture'])->whereAlphaNumeric('code')->name('referral.capture');

Route::middleware(['auth', 'client', 'verified'])->group(function () {
    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals.index');
    Route::get('/markets', [MarketController::class, 'index'])->name('markets.index');
    Route::get('/markets/data', [MarketController::class, 'data'])->name('markets.data');
    Route::get('/markets/price', [MarketController::class, 'price'])->middleware('throttle:120,1')->name('markets.price');
    Route::get('/markets/candles', [MarketController::class, 'candles'])->middleware('throttle:60,1')->name('markets.candles');
    Route::post('/markets/{symbol}/favorite', [MarketController::class, 'toggleFavorite'])->middleware('throttle:60,1')->name('markets.favorite');

    Route::get('/trading', [PaperTradingController::class, 'index'])->name('trading.index');
    Route::post('/trading/paper', [PaperTradingController::class, 'store'])->middleware('throttle:20,1')->name('trading.store');
    Route::post('/trading/settle-expired', [PaperTradingController::class, 'settleExpired'])->middleware('throttle:6,1')->name('trading.settle-expired');
    Route::post('/trading/paper/{trade}/settle', [PaperTradingController::class, 'settlePosition'])->whereNumber('trade')->middleware('throttle:60,1')->name('trading.settle-position');
    Route::get('/trading/paper/{trade}', [PaperTradingController::class, 'show'])->whereNumber('trade')->middleware('throttle:60,1')->name('trading.show');

    Route::get('/portfolio', PortfolioController::class)->name('portfolio.index');
    Route::get('/wallet-requests', [WalletRequestController::class, 'index'])->name('wallet-requests.index');
    Route::post('/wallet-requests/kyc', [WalletRequestController::class, 'submitKyc'])->middleware('throttle:3,60')->name('wallet-requests.kyc');
    Route::post('/wallet-requests/deposit', [WalletRequestController::class, 'deposit'])->middleware('throttle:5,60')->name('wallet-requests.deposit');
    Route::post('/wallet-requests/withdraw', [WalletRequestController::class, 'withdraw'])->middleware('throttle:5,60')->name('wallet-requests.withdraw');
    Route::post('/portfolio/demo/top-up', [DemoWalletController::class, 'topUp'])->middleware('throttle:5,1')->name('portfolio.demo.top-up');

    Route::get('/activity', ActivityController::class)->name('activity.index');
    Route::get('/statement', StatementController::class)->name('statement.index');
    Route::get('/activity/export', [ActivityController::class, 'export'])->name('activity.export');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->middleware('throttle:60,1')->name('notifications.read');

    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::post('/support', [SupportController::class, 'store'])->middleware('throttle:5,1')->name('support.store');
    Route::get('/support/{ticket}', [SupportController::class, 'show'])->name('support.show');
    Route::post('/support/{ticket}/messages', [SupportController::class, 'reply'])->middleware('throttle:10,1')->name('support.reply');

    Route::get('/security', [SecurityController::class, 'index'])->name('security.index');
    Route::post('/security/sessions/revoke-others', [SecurityController::class, 'revokeOthers'])->middleware('throttle:5,1')->name('security.sessions.revoke-others');
    Route::delete('/security/sessions/{session}', [SecurityController::class, 'revoke'])->middleware('throttle:10,1')->name('security.sessions.revoke');

    Route::get('/convert', [PaperConversionController::class, 'index'])->name('convert.index');
    Route::post('/convert/quote', [PaperConversionController::class, 'quote'])->middleware('throttle:30,1')->name('convert.quote');
    Route::post('/convert/paper', [PaperConversionController::class, 'convert'])->middleware('throttle:10,1')->name('convert.paper');
});
