<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OperationsController;
use App\Http\Controllers\Admin\SupportController;
use App\Http\Controllers\Admin\WalletOperationsController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', 'verified'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/clients', [ClientController::class, 'index'])->name('clients');
    Route::get('/clients/{client}', [ClientController::class, 'show'])->whereNumber('client')->name('clients.show');
    Route::get('/operations', OperationsController::class)->name('operations');
    Route::get('/wallet-operations', [WalletOperationsController::class, 'index'])->name('wallet-operations.index');
    Route::post('/wallet-operations/methods', [WalletOperationsController::class, 'saveMethod'])->name('wallet-operations.methods.store');
    Route::post('/wallet-operations/methods/{method}', [WalletOperationsController::class, 'toggleMethod'])->whereNumber('method')->name('wallet-operations.methods.update');
    Route::post('/wallet-operations/{walletRequest}/confirm-deposit', [WalletOperationsController::class, 'confirmDeposit'])->whereNumber('walletRequest')->name('wallet-operations.deposit.confirm');
    Route::post('/wallet-operations/{walletRequest}/reject', [WalletOperationsController::class, 'reject'])->whereNumber('walletRequest')->name('wallet-operations.reject');
    Route::post('/wallet-operations/{walletRequest}/mark-paid', [WalletOperationsController::class, 'markPaid'])->whereNumber('walletRequest')->name('wallet-operations.withdrawal.paid');
    Route::post('/wallet-operations/kyc/{submission}', [WalletOperationsController::class, 'reviewKyc'])->whereNumber('submission')->name('wallet-operations.kyc.review');
    Route::get('/wallet-operations/kyc/{submission}/files/{kind}', [WalletOperationsController::class, 'kycDocument'])->whereNumber('submission')->name('wallet-operations.kyc.file');
    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::get('/support/{ticket}', [SupportController::class, 'show'])->name('support.show');
    Route::post('/support/{ticket}/messages', [SupportController::class, 'reply'])->middleware('throttle:20,1')->name('support.reply');
});
