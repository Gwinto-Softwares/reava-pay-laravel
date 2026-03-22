<?php

use Illuminate\Support\Facades\Route;
use ReavaPay\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Reava Pay Admin Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the service provider and use the middleware
| configured in config/reava-pay.php. By default: ['web', 'auth', 'admin']
|
| For NPV: the 'admin' middleware checks AdminMiddleware + permissions.
| The 'permission:manage-billing' can be added per-route if needed.
|
*/

$prefix = config('reava-pay.admin_prefix', 'admin/reava-pay');
$middleware = config('reava-pay.admin_middleware', ['web', 'auth', 'admin']);

Route::prefix($prefix)->name('reava-pay.admin.')->middleware($middleware)->group(function () {
    Route::get('/', [AdminController::class, 'settings'])->name('settings');
    Route::post('/connect', [AdminController::class, 'connect'])->name('connect');
    Route::post('/disconnect', [AdminController::class, 'disconnect'])->name('disconnect');
    Route::post('/update', [AdminController::class, 'update'])->name('update');
    Route::post('/test-connection', [AdminController::class, 'testConnection'])->name('test-connection');
    Route::get('/transactions', [AdminController::class, 'transactions'])->name('transactions');
    Route::get('/transactions/{id}', [AdminController::class, 'transactionDetail'])->name('transactions.detail');
    Route::post('/transactions/{id}/sync', [AdminController::class, 'syncTransaction'])->name('transactions.sync');
});
