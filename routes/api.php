<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

// All internal REST endpoints live here (see Section 8 of the project brief).
// Every route responds with the { success, data, message } envelope via
// App\Exceptions\ApiExceptionRenderer.
//
// Session-authenticated (same-origin dashboard AJAX) — Sanctum stateful.
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/products/scan', [ProductController::class, 'scan'])->name('api.products.scan');

    Route::prefix('dashboard')->name('api.dashboard.')->group(function () {
        Route::get('/summary', [DashboardController::class, 'summary'])->name('summary');
        Route::get('/chart/stock-distribution', [DashboardController::class, 'stockDistribution'])->name('stock-distribution');
        Route::get('/chart/finance-summary', [DashboardController::class, 'financeSummary'])->name('finance-summary');
    });

    Route::get('/sales/recent', [DashboardController::class, 'recentSales'])->name('api.sales.recent');
});
