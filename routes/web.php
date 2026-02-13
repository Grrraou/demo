<?php

use App\Http\Controllers\Web\Admin\AdminCompanyController;
use App\Http\Controllers\Web\Admin\AdminEmployeeController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\Blog\ArticleController as BlogArticleController;
use App\Http\Controllers\Web\Customers\CustomerCompanyController;
use App\Http\Controllers\Web\Customers\CustomerContactController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\OwnedCompanySwitchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::post('/current-company', [OwnedCompanySwitchController::class, 'switch'])->name('current-company.switch');
    
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
    Route::get('/employees', [AdminEmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/{employee}', [AdminEmployeeController::class, 'show'])->name('employees.show');
    Route::put('/employees/{employee}', [AdminEmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [AdminEmployeeController::class, 'destroy'])->name('employees.destroy');

    Route::get('/companies', [AdminCompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/{ownedCompany}', [AdminCompanyController::class, 'show'])->name('companies.show');
    Route::put('/companies/{ownedCompany}', [AdminCompanyController::class, 'update'])->name('companies.update');
});

// Public blog (after auth routes so /blog/articles is not caught by /blog/{companySlug})
Route::get('/blog/{companySlug}', [BlogArticleController::class, 'indexPublic'])->name('blog.index.public');
Route::get('/blog/{companySlug}/{articleSlug}', [BlogArticleController::class, 'showPublic'])->name('blog.show');
