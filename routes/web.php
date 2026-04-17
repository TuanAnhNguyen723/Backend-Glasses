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
    Route::put('/api/products/{id}/featured', [\App\Http\Controllers\Admin\ProductController::class, 'toggleFeatured'])->name('admin.api.products.featured');
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

    // Order Management Routes
    Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('admin.orders');
    Route::get('/orders/{id}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('admin.orders.show');
    Route::get('/api/orders', [\App\Http\Controllers\Admin\OrderController::class, 'getOrders'])->name('admin.api.orders');
    Route::get('/api/orders/status-options', [\App\Http\Controllers\Admin\OrderController::class, 'getStatusOptions'])->name('admin.api.orders.status-options');
    Route::put('/api/orders/{id}/status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('admin.api.orders.update-status');

    // Promo Code Management Routes
    Route::get('/promo-codes', [\App\Http\Controllers\Admin\PromoCodeController::class, 'index'])->name('admin.promo-codes');
    Route::get('/api/promo-codes', [\App\Http\Controllers\Admin\PromoCodeController::class, 'getPromoCodes'])->name('admin.api.promo-codes');
    Route::post('/api/promo-codes', [\App\Http\Controllers\Admin\PromoCodeController::class, 'store'])->name('admin.api.promo-codes.store');
    Route::put('/api/promo-codes/{id}', [\App\Http\Controllers\Admin\PromoCodeController::class, 'update'])->name('admin.api.promo-codes.update');
    Route::post('/api/promo-codes/{id}/toggle-status', [\App\Http\Controllers\Admin\PromoCodeController::class, 'toggleStatus'])->name('admin.api.promo-codes.toggle-status');
    Route::delete('/api/promo-codes/{id}', [\App\Http\Controllers\Admin\PromoCodeController::class, 'destroy'])->name('admin.api.promo-codes.destroy');
    Route::get('/api/promo-codes/products/options', [\App\Http\Controllers\Admin\PromoCodeController::class, 'getProducts'])->name('admin.api.promo-codes.products');

    // Customer Management Routes
    Route::get('/customers', [\App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('admin.customers');
    Route::get('/customers/{id}', [\App\Http\Controllers\Admin\CustomerController::class, 'show'])->name('admin.customers.show');
    Route::get('/api/customers', [\App\Http\Controllers\Admin\CustomerController::class, 'getCustomers'])->name('admin.api.customers');
    Route::get('/api/customers/{id}', [\App\Http\Controllers\Admin\CustomerController::class, 'getCustomerDetail'])->name('admin.api.customers.detail');

    // Review Management Routes
    Route::get('/reviews', [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('admin.reviews');
    Route::get('/api/reviews', [\App\Http\Controllers\Admin\ReviewController::class, 'getReviews'])->name('admin.api.reviews');
    Route::put('/api/reviews/{id}/approval', [\App\Http\Controllers\Admin\ReviewController::class, 'toggleApproval'])->name('admin.api.reviews.approval');
    Route::post('/api/reviews/{id}/reply', [\App\Http\Controllers\Admin\ReviewController::class, 'reply'])->name('admin.api.reviews.reply');
    Route::put('/api/replies/{id}', [\App\Http\Controllers\Admin\ReviewController::class, 'updateReply'])->name('admin.api.replies.update');
});
