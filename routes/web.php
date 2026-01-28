<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\TransfersController;
use App\Http\Controllers\InventoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Sales Routes
Route::prefix('sales')->name('sales.')->group(function () {
    Route::get('/', [SalesController::class, 'index'])->name('index');
    Route::get('/create', [SalesController::class, 'create'])->name('create');
    Route::post('/', [SalesController::class, 'store'])->name('store');
    Route::get('/{sale}', [SalesController::class, 'show'])->name('show');
    Route::get('/store/{storeId}/inventory', [SalesController::class, 'getStoreInventory'])->name('store.inventory');
});

// Transfers Routes
Route::prefix('transfers')->name('transfers.')->group(function () {
    Route::get('/', [TransfersController::class, 'index'])->name('index');
    Route::get('/create', [TransfersController::class, 'create'])->name('create');
    Route::post('/', [TransfersController::class, 'store'])->name('store');
    Route::get('/{transfer}', [TransfersController::class, 'show'])->name('show');
    Route::post('/{transfer}/complete', [TransfersController::class, 'complete'])->name('complete');
    Route::post('/{transfer}/cancel', [TransfersController::class, 'cancel'])->name('cancel');
    Route::get('/store/{storeId}/inventory', [TransfersController::class, 'getStoreInventory'])->name('store.inventory');
});

// Inventory Routes
Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', [InventoryController::class, 'index'])->name('index');
    Route::get('/movements', [InventoryController::class, 'movements'])->name('movements');
    Route::get('/report', [InventoryController::class, 'report'])->name('report');
});