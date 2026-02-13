<?php

use App\Http\Controllers\Api\Admin\AdminApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Blog\ArticleController as BlogArticleController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\Inventory\CategoryController as InventoryCategoryController;
use App\Http\Controllers\Api\Inventory\LotBatchController;
use App\Http\Controllers\Api\Inventory\ProductController as InventoryProductController;
use App\Http\Controllers\Api\Inventory\StockController as InventoryStockController;
use App\Http\Controllers\Api\Inventory\StockLocationController as InventoryStockLocationController;
use App\Http\Controllers\Api\Inventory\StockMovementController as InventoryStockMovementController;
use App\Http\Controllers\Api\Inventory\SupplierController as InventorySupplierController;
use App\Http\Controllers\Api\Inventory\UnitController as InventoryUnitController;
use App\Http\Controllers\Api\Sales\DeliveryController as SalesDeliveryController;
use App\Http\Controllers\Api\Sales\InvoiceController as SalesInvoiceController;
use App\Http\Controllers\Api\Sales\PaymentController as SalesPaymentController;
use App\Http\Controllers\Api\Sales\QuoteController as SalesQuoteController;
use App\Http\Controllers\Api\Sales\SalesOrderController as SalesOrderController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::apiResource('customers', CustomerController::class);

    Route::middleware('manage.articles')->prefix('blog')->group(function () {
        Route::get('articles', [BlogArticleController::class, 'index']);
        Route::get('articles/{article}', [BlogArticleController::class, 'show']);
    });

    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('roles', [AdminApiController::class, 'roles'])->name('admin.roles');
        Route::apiResource('team-members', AdminApiController::class)->only(['index', 'show', 'update', 'destroy']);
    });

    // Inventory module (view.inventory = read, edit.inventory = write)
    Route::middleware('view.inventory')->prefix('inventory')->name('inventory.')->group(function () {
        Route::get('categories/tree', [InventoryCategoryController::class, 'tree'])->name('categories.tree');
        Route::get('categories', [InventoryCategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/{category}', [InventoryCategoryController::class, 'show'])->name('categories.show');
        Route::get('products', [InventoryProductController::class, 'index'])->name('products.index');
        Route::get('products/{product}', [InventoryProductController::class, 'show'])->name('products.show');
        Route::get('suppliers', [InventorySupplierController::class, 'index'])->name('suppliers.index');
        Route::get('suppliers/{supplier}', [InventorySupplierController::class, 'show'])->name('suppliers.show');
        Route::get('stock-locations', [InventoryStockLocationController::class, 'index'])->name('stock-locations.index');
        Route::get('stock-locations/{stockLocation}', [InventoryStockLocationController::class, 'show'])->name('stock-locations.show')->scopeBindings();
        Route::get('stocks', [InventoryStockController::class, 'index'])->name('stocks.index');
        Route::get('stocks/{stock}/movements', [InventoryStockController::class, 'movements'])->name('stocks.movements');
        Route::get('stocks/{stock}', [InventoryStockController::class, 'show'])->name('stocks.show');
        Route::get('stock-movements', [InventoryStockMovementController::class, 'index'])->name('stock-movements.index');
        Route::get('stock-movements/{stockMovement}', [InventoryStockMovementController::class, 'show'])->name('stock-movements.show')->scopeBindings();
        Route::get('lot-batches', [LotBatchController::class, 'index'])->name('lot-batches.index');
        Route::get('lot-batches/{lotBatch}', [LotBatchController::class, 'show'])->name('lot-batches.show')->scopeBindings();
        Route::get('units', [InventoryUnitController::class, 'index'])->name('units.index');
        Route::get('units/{unit}', [InventoryUnitController::class, 'show'])->name('units.show');

        Route::middleware('edit.inventory')->group(function () {
            Route::post('categories', [InventoryCategoryController::class, 'store'])->name('categories.store');
            Route::put('categories/{category}', [InventoryCategoryController::class, 'update'])->name('categories.update');
            Route::delete('categories/{category}', [InventoryCategoryController::class, 'destroy'])->name('categories.destroy');
            Route::post('products', [InventoryProductController::class, 'store'])->name('products.store');
            Route::put('products/{product}', [InventoryProductController::class, 'update'])->name('products.update');
            Route::delete('products/{product}', [InventoryProductController::class, 'destroy'])->name('products.destroy');
            Route::post('suppliers', [InventorySupplierController::class, 'store'])->name('suppliers.store');
            Route::put('suppliers/{supplier}', [InventorySupplierController::class, 'update'])->name('suppliers.update');
            Route::delete('suppliers/{supplier}', [InventorySupplierController::class, 'destroy'])->name('suppliers.destroy');
            Route::post('stock-locations', [InventoryStockLocationController::class, 'store'])->name('stock-locations.store');
            Route::put('stock-locations/{stockLocation}', [InventoryStockLocationController::class, 'update'])->name('stock-locations.update');
            Route::delete('stock-locations/{stockLocation}', [InventoryStockLocationController::class, 'destroy'])->name('stock-locations.destroy');
            Route::post('stocks/entry', [InventoryStockController::class, 'entry'])->name('stocks.entry');
            Route::post('stocks/exit', [InventoryStockController::class, 'exit'])->name('stocks.exit');
            Route::post('stocks/transfer', [InventoryStockController::class, 'transfer'])->name('stocks.transfer');
            Route::post('lot-batches', [LotBatchController::class, 'store'])->name('lot-batches.store');
            Route::put('lot-batches/{lotBatch}', [LotBatchController::class, 'update'])->name('lot-batches.update');
            Route::delete('lot-batches/{lotBatch}', [LotBatchController::class, 'destroy'])->name('lot-batches.destroy');
            Route::post('units', [InventoryUnitController::class, 'store'])->name('units.store');
            Route::put('units/{unit}', [InventoryUnitController::class, 'update'])->name('units.update');
            Route::delete('units/{unit}', [InventoryUnitController::class, 'destroy'])->name('units.destroy');
        });
    });

    // Sales module
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::post('quotes/{quote}/send', [SalesQuoteController::class, 'send'])->name('quotes.send');
        Route::post('quotes/{quote}/accept', [SalesQuoteController::class, 'accept'])->name('quotes.accept');
        Route::post('quotes/{quote}/reject', [SalesQuoteController::class, 'reject'])->name('quotes.reject');
        Route::apiResource('quotes', SalesQuoteController::class)->only(['index', 'store', 'show']);

        Route::post('orders/{salesOrder}/reserve-stock', [SalesOrderController::class, 'reserveStock'])->name('orders.reserve-stock');
        Route::apiResource('orders', SalesOrderController::class)->only(['index', 'store', 'show'])->parameters(['orders' => 'salesOrder']);

        Route::post('deliveries/{delivery}/mark-delivered', [SalesDeliveryController::class, 'markDelivered'])->name('deliveries.mark-delivered');
        Route::apiResource('deliveries', SalesDeliveryController::class)->only(['index', 'store', 'show']);

        Route::post('invoices/{invoice}/mark-sent', [SalesInvoiceController::class, 'markSent'])->name('invoices.mark-sent');
        Route::post('invoices/{invoice}/mark-paid', [SalesInvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
        Route::apiResource('invoices', SalesInvoiceController::class)->only(['index', 'store', 'show']);

        Route::post('payments', [SalesPaymentController::class, 'store'])->name('payments.store');
        Route::get('payments/{payment}', [SalesPaymentController::class, 'show'])->name('payments.show');
    });
});
