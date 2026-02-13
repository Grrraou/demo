<?php

use App\Http\Controllers\Api\Admin\AdminApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Blog\ArticleController as BlogArticleController;
use App\Http\Controllers\Api\CustomerController;
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
        Route::apiResource('employees', AdminApiController::class)->only(['index', 'show', 'update', 'destroy']);
    });
});
