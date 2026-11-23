<?php

use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Guests\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('auth')->get('/dashboard', function () {
    if (auth()->user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    abort_unless(auth()->user()->role === 'client', 403);

    return app()->call([ClientDashboardController::class, '__invoke']);
})->name('dashboard');

require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
require __DIR__.'/client.php';
require __DIR__.'/admin.php';
