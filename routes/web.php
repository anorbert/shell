<?php

use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Guests\HomeController;
use App\Http\Controllers\Guests\SupportChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::prefix('support/chat')->name('guest.support.')->controller(SupportChatController::class)->group(function () {
    Route::post('/', 'start')->middleware('throttle:3,1')->name('start');
    Route::get('/{ticket}', 'messages')->whereNumber('ticket')->middleware('throttle:30,1')->name('messages');
    Route::post('/{ticket}/messages', 'reply')->whereNumber('ticket')->middleware('throttle:10,1')->name('reply');
});

Route::middleware('auth')->get('/dashboard', function () {
    if (auth()->user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    abort_unless(auth()->user()->role === 'client', 403);

    $controller = app(ClientDashboardController::class);
    return app()->call([$controller, '__invoke']);
})->name('dashboard');

require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
require __DIR__.'/client.php';
require __DIR__.'/admin.php';
