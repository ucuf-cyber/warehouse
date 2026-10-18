<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\BarcodeController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\MarketplaceController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\RackController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StockOpnameController;
use App\Http\Controllers\Admin\StokController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('produk')->name('produk.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/data', [ProductController::class, 'data'])->name('data');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/barcode/cetak', [BarcodeController::class, 'print'])->name('barcode.print');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');

        Route::get('/kategori/data', [CategoryController::class, 'data'])->name('kategori.data');
        Route::post('/kategori', [CategoryController::class, 'store'])->name('kategori.store');
        Route::get('/kategori/{kategori}', [CategoryController::class, 'show'])->name('kategori.show');
        Route::put('/kategori/{kategori}', [CategoryController::class, 'update'])->name('kategori.update');
        Route::delete('/kategori/{kategori}', [CategoryController::class, 'destroy'])->name('kategori.destroy');

        Route::get('/brand/data', [BrandController::class, 'data'])->name('brand.data');
        Route::post('/brand', [BrandController::class, 'store'])->name('brand.store');
        Route::get('/brand/{brand}', [BrandController::class, 'show'])->name('brand.show');
        Route::put('/brand/{brand}', [BrandController::class, 'update'])->name('brand.update');
        Route::delete('/brand/{brand}', [BrandController::class, 'destroy'])->name('brand.destroy');

        Route::get('/supplier/data', [SupplierController::class, 'data'])->name('supplier.data');
        Route::post('/supplier', [SupplierController::class, 'store'])->name('supplier.store');
        Route::get('/supplier/{supplier}', [SupplierController::class, 'show'])->name('supplier.show');
        Route::put('/supplier/{supplier}', [SupplierController::class, 'update'])->name('supplier.update');
        Route::delete('/supplier/{supplier}', [SupplierController::class, 'destroy'])->name('supplier.destroy');
    });

    Route::view('/scan', 'admin.scan.index')->name('scan.index');

    Route::prefix('stok')->name('stok.')->group(function () {
        Route::get('/', [StokController::class, 'index'])->name('index');
        Route::get('/data', [StokController::class, 'data'])->name('data');
        Route::post('/in', [StokController::class, 'storeIn'])->name('in');
        Route::post('/out', [StokController::class, 'storeOut'])->name('out');
        Route::post('/adjustment', [StokController::class, 'storeAdjustment'])->name('adjustment');
        Route::post('/mutasi', [StokController::class, 'storeMutasi'])->name('mutasi');

        Route::get('/rak/data', [RackController::class, 'data'])->name('rak.data');
        Route::post('/rak', [RackController::class, 'store'])->name('rak.store');
        Route::get('/rak/{rak}', [RackController::class, 'show'])->name('rak.show');
        Route::put('/rak/{rak}', [RackController::class, 'update'])->name('rak.update');
        Route::delete('/rak/{rak}', [RackController::class, 'destroy'])->name('rak.destroy');

        Route::post('/sub-rak', [RackController::class, 'subRackStore'])->name('subrak.store');
        Route::get('/sub-rak/{subRak}', [RackController::class, 'subRackShow'])->name('subrak.show');
        Route::put('/sub-rak/{subRak}', [RackController::class, 'subRackUpdate'])->name('subrak.update');
        Route::delete('/sub-rak/{subRak}', [RackController::class, 'subRackDestroy'])->name('subrak.destroy');

        Route::prefix('opname')->name('opname.')->group(function () {
            Route::get('/', [StockOpnameController::class, 'index'])->name('index');
            Route::post('/', [StockOpnameController::class, 'store'])->name('store');
            Route::get('/{opname}', [StockOpnameController::class, 'show'])->name('show');
            Route::get('/{opname}/find/{code}', [StockOpnameController::class, 'findItem'])->name('find');
            Route::patch('/{opname}/item/{item}', [StockOpnameController::class, 'updateItem'])->name('item.update');
            Route::post('/{opname}/finalize', [StockOpnameController::class, 'finalize'])->name('finalize');
        });

        Route::prefix('analitik')->name('analitik.')->group(function () {
            Route::get('/stock-out', [AnalyticsController::class, 'stockOut'])->name('stockout');
            Route::get('/restock', [AnalyticsController::class, 'restock'])->name('restock');
            Route::get('/slow-moving', [AnalyticsController::class, 'slowMoving'])->name('slowmoving');
        });
    });

    Route::prefix('pembelian')->name('pembelian.')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('index');
        Route::get('/data', [PurchaseController::class, 'data'])->name('data');
        Route::post('/', [PurchaseController::class, 'store'])->name('store');
        Route::get('/{purchase}', [PurchaseController::class, 'show'])->name('show');
        Route::post('/{purchase}/terima', [PurchaseController::class, 'markReceived'])->name('terima');
        Route::put('/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('cancel');
        Route::post('/{purchase}/pembayaran', [PurchaseController::class, 'storePayment'])->name('pembayaran');
        Route::post('/{purchase}/nota', [PurchaseController::class, 'uploadNota'])->name('nota');
        Route::post('/{purchase}/retur', [PurchaseController::class, 'storeReturn'])->name('retur');
    });

    Route::prefix('penjualan')->name('penjualan.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::get('/data', [SaleController::class, 'data'])->name('data');
        Route::post('/', [SaleController::class, 'store'])->name('store');
        Route::get('/{sale}', [SaleController::class, 'show'])->name('show');
        Route::put('/{sale}/cancel', [SaleController::class, 'cancel'])->name('cancel');
    });

    Route::prefix('marketplace')->name('marketplace.')->group(function () {
        Route::get('/', [MarketplaceController::class, 'index'])->name('index');
        Route::get('/template', [MarketplaceController::class, 'template'])->name('template');
        Route::post('/import', [MarketplaceController::class, 'import'])->name('import');

        Route::get('/mapping/data', [MarketplaceController::class, 'mappingData'])->name('mapping.data');
        Route::post('/mapping', [MarketplaceController::class, 'mappingStore'])->name('mapping.store');
        Route::get('/mapping/{mapping}', [MarketplaceController::class, 'mappingShow'])->name('mapping.show');
        Route::put('/mapping/{mapping}', [MarketplaceController::class, 'mappingUpdate'])->name('mapping.update');
        Route::delete('/mapping/{mapping}', [MarketplaceController::class, 'mappingDestroy'])->name('mapping.destroy');
    });

    Route::prefix('keuangan')->name('keuangan.')->group(function () {
        Route::get('/', [FinanceController::class, 'index'])->name('index');
        Route::get('/summary', [FinanceController::class, 'summary'])->name('summary');
        Route::get('/data', [FinanceController::class, 'data'])->name('data');
        Route::post('/', [FinanceController::class, 'store'])->name('store');
        Route::delete('/{keuangan}', [FinanceController::class, 'destroy'])->name('destroy');
        Route::get('/laporan/laba-rugi', [FinanceController::class, 'profitLoss'])->name('laba-rugi');
        Route::get('/laporan/cashflow', [FinanceController::class, 'cashflow'])->name('cashflow');
        Route::get('/laporan/neraca', [FinanceController::class, 'balanceSheet'])->name('neraca');
    });

    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/riwayat', [ReportController::class, 'history'])->name('history');
        Route::get('/export-sync', [ReportController::class, 'exportSync'])->name('export-sync');
        Route::post('/export-async', [ReportController::class, 'exportAsync'])->name('export-async');
        Route::get('/{report}/download', [ReportController::class, 'download'])->name('download');
    });

    Route::prefix('pengaturan')->name('pengaturan.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::put('/profil', [SettingController::class, 'updateProfile'])->name('profil');
        Route::put('/printer', [SettingController::class, 'updatePrinter'])->name('printer');

        Route::get('/backup', [SettingController::class, 'backupIndex'])->name('backup.index');
        Route::post('/backup/run', [SettingController::class, 'backupRun'])->name('backup.run');
        Route::get('/backup/download', [SettingController::class, 'backupDownload'])->name('backup.download');
        Route::delete('/backup', [SettingController::class, 'backupDelete'])->name('backup.delete');

        Route::get('/log/data', [SettingController::class, 'activityLogData'])->name('log.data');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
