<?php

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Money Tracker API endpoints.
| All routes are prefixed with /api automatically.
| No authentication is required for this assessment.
|
*/

/** User endpoints */
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');

/** Wallet endpoints (nested under users for creation & listing) */
Route::get('/users/{user}/wallets', [WalletController::class, 'index'])->name('users.wallets.index');
Route::post('/users/{user}/wallets', [WalletController::class, 'store'])->name('users.wallets.store');
Route::get('/wallets/{wallet}', [WalletController::class, 'show'])->name('wallets.show');

/** Transaction endpoints (nested under wallets) */
Route::get('/wallets/{wallet}/transactions', [TransactionController::class, 'index'])->name('wallets.transactions.index');
Route::post('/wallets/{wallet}/transactions', [TransactionController::class, 'store'])->name('wallets.transactions.store');
