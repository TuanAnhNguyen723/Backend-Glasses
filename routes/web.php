<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/api/stats', [DashboardController::class, 'getStats'])->name('admin.api.stats');
    Route::get('/api/sales-overview', [DashboardController::class, 'getSalesOverview'])->name('admin.api.sales-overview');
    Route::get('/api/category-stats', [DashboardController::class, 'getCategoryStats'])->name('admin.api.category-stats');
    Route::get('/api/recent-orders', [DashboardController::class, 'getRecentOrders'])->name('admin.api.recent-orders');
    
    // Product Management Routes
    Route::get('/products', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('admin.products');
    Route::get('/products/create', [\App\Http\Controllers\Admin\ProductController::class, 'create'])->name('admin.products.create');
    Route::post('/api/products', [\App\Http\Controllers\Admin\ProductController::class, 'store'])->name('admin.api.products.store');
    Route::get('/api/products', [\App\Http\Controllers\Admin\ProductController::class, 'getProducts'])->name('admin.api.products');
    Route::get('/api/products/stats', [\App\Http\Controllers\Admin\ProductController::class, 'getStats'])->name('admin.api.products.stats');
    Route::get('/api/products/filters', [\App\Http\Controllers\Admin\ProductController::class, 'getFilters'])->name('admin.api.products.filters');
});
