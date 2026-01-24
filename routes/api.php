<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PrescriptionController;
use App\Http\Controllers\Api\SavedStyleController;
use App\Http\Controllers\Api\ReviewController;

// Public routes
Route::prefix('v1')->group(function () {
    // Auth
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    
    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/categories', [ProductController::class, 'categories']);
    
    // Reviews
    Route::get('/products/{id}/reviews', [ReviewController::class, 'index']);
});

// Protected routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // User Profile
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::post('/profile/avatar', [UserController::class, 'updateAvatar']);
    
    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clear']);
    
    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::get('/orders/{id}/track', [OrderController::class, 'track']);
    
    // Prescriptions
    Route::get('/prescriptions', [PrescriptionController::class, 'index']);
    Route::post('/prescriptions', [PrescriptionController::class, 'store']);
    Route::put('/prescriptions/{id}', [PrescriptionController::class, 'update']);
    Route::delete('/prescriptions/{id}', [PrescriptionController::class, 'destroy']);
    
    // Saved Styles (Wishlist)
    Route::get('/saved-styles', [SavedStyleController::class, 'index']);
    Route::post('/saved-styles', [SavedStyleController::class, 'store']);
    Route::delete('/saved-styles/{id}', [SavedStyleController::class, 'destroy']);
    Route::delete('/saved-styles', [SavedStyleController::class, 'clear']);
    
    // Reviews
    Route::post('/products/{id}/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    
    // Promo Codes
    Route::post('/promo-codes/validate', [OrderController::class, 'validatePromoCode']);
});
