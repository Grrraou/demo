<?php

use App\Http\Controllers\Web\Admin\AdminCompanyController;
use App\Http\Controllers\Web\Admin\AdminController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CustomerCompanyController;
use App\Http\Controllers\Web\CustomerContactController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\OwnedCompanySwitchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::post('/current-company', [OwnedCompanySwitchController::class, 'switch'])->name('current-company.switch');
    Route::get('/customers', [CustomerCompanyController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customerCompany}', [CustomerCompanyController::class, 'show'])->name('customers.show');
    Route::get('/contacts', [CustomerContactController::class, 'index'])->name('contacts.index');

    Route::middleware(['edit.customers'])->group(function () {
        Route::get('/customers/{customerCompany}/edit', [CustomerCompanyController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customerCompany}', [CustomerCompanyController::class, 'update'])->name('customers.update');
        Route::get('/contacts/{customerContact}/edit', [CustomerContactController::class, 'edit'])->name('contacts.edit');
        Route::put('/contacts/{customerContact}', [CustomerContactController::class, 'update'])->name('contacts.update');
    });
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [AdminController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroy'])->name('users.destroy');

    Route::get('/companies', [AdminCompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/{ownedCompany}', [AdminCompanyController::class, 'show'])->name('companies.show');
    Route::put('/companies/{ownedCompany}', [AdminCompanyController::class, 'update'])->name('companies.update');
});
