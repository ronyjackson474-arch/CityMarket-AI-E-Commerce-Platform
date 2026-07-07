<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AiSearchController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\ReturnController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Admin\AiAssistantController as AdminAiAssistantController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{slug}', [CategoryController::class, 'show']);

Route::get('products', [ProductController::class, 'index']);
Route::get('products/featured', [ProductController::class, 'featured']);
Route::get('products/flash-deals', [ProductController::class, 'flashDeals']);
Route::get('products/{slug}', [ProductController::class, 'show']);

Route::get('products/{slug}/reviews', [ReviewController::class, 'index']);

Route::post('ai/search', [AiSearchController::class, 'search']);

// Authenticated endpoints (mobile token auth)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    Route::get('cart', [CartController::class, 'index']);
    Route::post('cart/add', [CartController::class, 'add']);
    Route::post('cart/update', [CartController::class, 'update']);
    Route::post('cart/remove', [CartController::class, 'remove']);
    Route::post('cart/clear', [CartController::class, 'clear']);

    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{orderNumber}', [OrderController::class, 'show']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::post('orders/{orderNumber}/cancel', [OrderController::class, 'cancel']);
    Route::post('orders/{orderNumber}/return', [ReturnController::class, 'store']);

    // Reviews
    Route::post('products/{slug}/reviews', [ReviewController::class, 'store']);

    // Wishlist
    Route::get('wishlist', [WishlistController::class, 'index']);
    Route::post('wishlist/add', [WishlistController::class, 'add']);
    Route::post('wishlist/remove', [WishlistController::class, 'remove']);

    // Addresses
    Route::get('addresses', [AddressController::class, 'index']);
    Route::post('addresses', [AddressController::class, 'store']);
    Route::put('addresses/{address}', [AddressController::class, 'update']);
    Route::delete('addresses/{address}', [AddressController::class, 'destroy']);

    // Wallet
    Route::get('wallet', [WalletController::class, 'show']);
    Route::get('wallet/transactions', [WalletController::class, 'transactions']);
    Route::post('referrals/apply', [WalletController::class, 'applyReferral']);

    // AI Chat
    Route::get('chat/conversations', [ChatController::class, 'conversations']);
    Route::post('chat/send', [ChatController::class, 'send']);

    // Recommendations
    Route::get('recommendations', [RecommendationController::class, 'index']);
});

// AI Admin (admin-only via sanctum)
Route::middleware(['auth:sanctum'])->prefix('admin/ai')->group(function () {
    Route::post('query', function (\Illuminate\Http\Request $request) {
        abort_unless($request->user()->isAdmin(), 403, 'Admin access required.');
        return app(AdminAiAssistantController::class)->query($request, app(\App\Services\AiAdminAssistantService::class));
    });
});
