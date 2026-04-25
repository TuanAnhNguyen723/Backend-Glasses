<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PromoCodeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PrescriptionController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\AiRecommendationController;
use App\Http\Controllers\Api\SettingController;

// Public routes
Route::prefix('v1')->group(function () {
    // Auth
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    
    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/related', [ProductController::class, 'related']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::put('/products/{id}/feature', [ProductController::class, 'toggleFeatured']);
    Route::get('/categories', [ProductController::class, 'categories']);
    
    // Reviews
    Route::get('/products/{id}/reviews', [ReviewController::class, 'index']);
    Route::get('/settings/general', [SettingController::class, 'general']);

    // Guest: tạo đơn không cần đăng nhập
    Route::post('/orders', [OrderController::class, 'store']);
    // Guest: ghi nhận thanh toán sau redirect Momo/VNPay (xác thực bằng order_number + shipping_email)
    Route::post('/orders/confirm-payment-guest', [OrderController::class, 'recordPaymentGuest']);
    Route::post('/ai/recommend-products', [AiRecommendationController::class, 'recommend'])->middleware('throttle:20,1');

});

// Protected routes (cần đăng nhập)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // User Profile & Dashboard (dùng /profile thay /me - cùng mục đích, profile có format tối ưu hơn)
    Route::get('/dashboard', [UserController::class, 'dashboard']);
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::post('/profile/avatar', [UserController::class, 'updateAvatar']);
    
    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clear']);
    
    // Orders (đã đăng nhập)
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}/payment', [OrderController::class, 'recordPayment']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::get('/orders/{id}/track', [OrderController::class, 'track']);
    
    // Prescriptions
    Route::get('/prescriptions', [PrescriptionController::class, 'index']);
    Route::post('/prescriptions', [PrescriptionController::class, 'store']);
    Route::put('/prescriptions/{id}', [PrescriptionController::class, 'update']);
    Route::delete('/prescriptions/{id}', [PrescriptionController::class, 'destroy']);
    
    // Reviews
    Route::get('/products/{id}/my-review', [ReviewController::class, 'myReview']);
    Route::post('/products/{id}/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    
    // Promo Codes
    Route::post('/promo-codes/validate', [OrderController::class, 'validatePromoCode']);
    Route::get('/promo-codes/available', [PromoCodeController::class, 'available']);
    Route::post('/promo-codes/{id}/claim', [PromoCodeController::class, 'claim']);
    Route::get('/promo-codes/my-vouchers', [PromoCodeController::class, 'myVouchers']);
});

