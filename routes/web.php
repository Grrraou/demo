<?php

use App\Http\Controllers\Web\Admin\AdminCompanyController;
use App\Http\Controllers\Web\Admin\AdminTeamMemberController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\Blog\ArticleController as BlogArticleController;
use App\Http\Controllers\Web\Customers\CustomerCompanyController;
use App\Http\Controllers\Web\Customers\CustomerContactController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\Inventory\CategoryController as InventoryCategoryController;
use App\Http\Controllers\Web\Inventory\ProductController as InventoryProductController;
use App\Http\Controllers\Web\Inventory\StockController as InventoryStockController;
use App\Http\Controllers\Web\Inventory\StockLocationController as InventoryStockLocationController;
use App\Http\Controllers\Web\Inventory\SupplierController as InventorySupplierController;
use App\Http\Controllers\Web\Inventory\UnitController as InventoryUnitController;
use App\Http\Controllers\Web\OwnedCompanySwitchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::post('/current-company', [OwnedCompanySwitchController::class, 'switch'])->name('current-company.switch');

    Route::middleware(['view.inventory'])->prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/products', [InventoryProductController::class, 'index'])->name('products.index');
        Route::get('/categories', [InventoryCategoryController::class, 'index'])->name('categories.index');
        Route::get('/suppliers', [InventorySupplierController::class, 'index'])->name('suppliers.index');
        Route::get('/stock-locations', [InventoryStockLocationController::class, 'index'])->name('stock-locations.index');
        Route::get('/stocks', [InventoryStockController::class, 'index'])->name('stocks.index');
        Route::get('/units', [InventoryUnitController::class, 'index'])->name('units.index');

        Route::middleware(['edit.inventory'])->group(function () {
            Route::get('/products/create', [InventoryProductController::class, 'create'])->name('products.create');
            Route::post('/products', [InventoryProductController::class, 'store'])->name('products.store');
            Route::get('/products/{product}/edit', [InventoryProductController::class, 'edit'])->name('products.edit');
            Route::put('/products/{product}', [InventoryProductController::class, 'update'])->name('products.update');
            Route::get('/categories/create', [InventoryCategoryController::class, 'create'])->name('categories.create');
            Route::post('/categories', [InventoryCategoryController::class, 'store'])->name('categories.store');
            Route::get('/categories/{category}/edit', [InventoryCategoryController::class, 'edit'])->name('categories.edit');
            Route::put('/categories/{category}', [InventoryCategoryController::class, 'update'])->name('categories.update');
            Route::get('/suppliers/create', [InventorySupplierController::class, 'create'])->name('suppliers.create');
            Route::post('/suppliers', [InventorySupplierController::class, 'store'])->name('suppliers.store');
            Route::get('/suppliers/{supplier}/edit', [InventorySupplierController::class, 'edit'])->name('suppliers.edit');
            Route::put('/suppliers/{supplier}', [InventorySupplierController::class, 'update'])->name('suppliers.update');
            Route::get('/stock-locations/create', [InventoryStockLocationController::class, 'create'])->name('stock-locations.create');
            Route::post('/stock-locations', [InventoryStockLocationController::class, 'store'])->name('stock-locations.store');
            Route::get('/stock-locations/{stockLocation}/edit', [InventoryStockLocationController::class, 'edit'])->name('stock-locations.edit');
            Route::put('/stock-locations/{stockLocation}', [InventoryStockLocationController::class, 'update'])->name('stock-locations.update');
            Route::get('/units/create', [InventoryUnitController::class, 'create'])->name('units.create');
            Route::post('/units', [InventoryUnitController::class, 'store'])->name('units.store');
            Route::get('/units/{unit}/edit', [InventoryUnitController::class, 'edit'])->name('units.edit');
            Route::put('/units/{unit}', [InventoryUnitController::class, 'update'])->name('units.update');
        });
    });

    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/companies', [CustomerCompanyController::class, 'index'])->name('companies.index');
        Route::get('/companies/{customerCompany}', [CustomerCompanyController::class, 'show'])->name('companies.show');
        Route::get('/contacts', [CustomerContactController::class, 'index'])->name('contacts.index');

        Route::middleware(['edit.customers'])->group(function () {
            Route::get('/companies/{customerCompany}/edit', [CustomerCompanyController::class, 'edit'])->name('companies.edit');
            Route::put('/companies/{customerCompany}', [CustomerCompanyController::class, 'update'])->name('companies.update');
            Route::get('/contacts/{customerContact}/edit', [CustomerContactController::class, 'edit'])->name('contacts.edit');
            Route::put('/contacts/{customerContact}', [CustomerContactController::class, 'update'])->name('contacts.update');
        });
    });

    Route::middleware(['manage.articles'])->prefix('blog')->name('blog.')->group(function () {
        Route::get('/articles/keywords', [BlogArticleController::class, 'keywords'])->name('articles.keywords');
        Route::get('/articles', [BlogArticleController::class, 'index'])->name('articles.index');
        Route::get('/articles/create', [BlogArticleController::class, 'create'])->name('articles.create');
        Route::post('/articles', [BlogArticleController::class, 'store'])->name('articles.store');
        Route::get('/articles/{article}/edit', [BlogArticleController::class, 'edit'])->name('articles.edit');
        Route::put('/articles/{article}', [BlogArticleController::class, 'update'])->name('articles.update');
        Route::post('/articles/{article}/publish', [BlogArticleController::class, 'publish'])->name('articles.publish');
        Route::post('/articles/{article}/unpublish', [BlogArticleController::class, 'unpublish'])->name('articles.unpublish');
        Route::delete('/articles/{article}', [BlogArticleController::class, 'destroy'])->name('articles.destroy');
    });
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/team-members', [AdminTeamMemberController::class, 'index'])->name('team-members.index');
    Route::get('/team-members/{teamMember}', [AdminTeamMemberController::class, 'show'])->name('team-members.show');
    Route::put('/team-members/{teamMember}', [AdminTeamMemberController::class, 'update'])->name('team-members.update');
    Route::delete('/team-members/{teamMember}', [AdminTeamMemberController::class, 'destroy'])->name('team-members.destroy');

    Route::get('/companies', [AdminCompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/{ownedCompany}', [AdminCompanyController::class, 'show'])->name('companies.show');
    Route::put('/companies/{ownedCompany}', [AdminCompanyController::class, 'update'])->name('companies.update');
});

// Public blog (after auth routes so /blog/articles is not caught by /blog/{companySlug})
Route::get('/blog/{companySlug}', [BlogArticleController::class, 'indexPublic'])->name('blog.index.public');
Route::get('/blog/{companySlug}/{articleSlug}', [BlogArticleController::class, 'showPublic'])->name('blog.show');
