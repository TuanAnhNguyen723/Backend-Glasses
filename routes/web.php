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
    Route::get('/products/{id}/edit', [\App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('admin.products.edit');
    Route::post('/api/products', [\App\Http\Controllers\Admin\ProductController::class, 'store'])->name('admin.api.products.store');
    Route::put('/api/products/{id}', [\App\Http\Controllers\Admin\ProductController::class, 'update'])->name('admin.api.products.update');
    Route::delete('/api/products/{id}', [\App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('admin.api.products.destroy');
    Route::get('/api/products/filters', [\App\Http\Controllers\Admin\ProductController::class, 'getFilters'])->name('admin.api.products.filters');
    Route::get('/api/products/stats', [\App\Http\Controllers\Admin\ProductController::class, 'getStats'])->name('admin.api.products.stats');
    Route::get('/api/products/{id}', [\App\Http\Controllers\Admin\ProductController::class, 'show'])->name('admin.api.products.show');
    Route::get('/api/products', [\App\Http\Controllers\Admin\ProductController::class, 'getProducts'])->name('admin.api.products');
    
    // Brand Management Routes
    Route::get('/brands', [\App\Http\Controllers\Admin\BrandController::class, 'index'])->name('admin.brands');
    Route::get('/api/brands', [\App\Http\Controllers\Admin\BrandController::class, 'getBrands'])->name('admin.api.brands');
    Route::post('/api/brands', [\App\Http\Controllers\Admin\BrandController::class, 'store'])->name('admin.api.brands.store');
    Route::put('/api/brands/{id}', [\App\Http\Controllers\Admin\BrandController::class, 'update'])->name('admin.api.brands.update');
    Route::post('/api/brands/{id}/toggle-status', [\App\Http\Controllers\Admin\BrandController::class, 'toggleStatus'])->name('admin.api.brands.toggle-status');
    Route::delete('/api/brands/{id}', [\App\Http\Controllers\Admin\BrandController::class, 'destroy'])->name('admin.api.brands.destroy');
});
