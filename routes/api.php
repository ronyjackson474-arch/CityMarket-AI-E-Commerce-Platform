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
use App\Http\Controllers\Api\StorefrontApiController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Admin\AiAssistantController as AdminAiAssistantController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [\App\Http\Controllers\Api\AuthExtendedController::class, 'forgotPassword']);
    Route::post('reset-password', [\App\Http\Controllers\Api\AuthExtendedController::class, 'resetPassword']);
});

Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{slug}', [CategoryController::class, 'show']);

Route::get('products', [ProductController::class, 'index']);
Route::get('products/featured', [ProductController::class, 'featured']);
Route::get('products/flash-deals', [ProductController::class, 'flashDeals']);
Route::get('products/{slug}', [ProductController::class, 'show']);
Route::get('products/{slug}/related', [StorefrontApiController::class, 'related']);

// Storefront: home, vendors, coupons, checkout (mobile app)
Route::get('home', [StorefrontApiController::class, 'home']);
Route::get('vendors', [StorefrontApiController::class, 'vendors']);
Route::get('vendors/{slug}', [StorefrontApiController::class, 'vendorShow']);
Route::post('coupons/validate', [StorefrontApiController::class, 'validateCoupon'])->middleware('auth:sanctum');

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
    Route::get('cart/count', [StorefrontApiController::class, 'cartCount']);
    Route::post('cart/toggle-wishlist', [StorefrontApiController::class, 'toggleWishlist']);
    Route::get('checkout/summary', [StorefrontApiController::class, 'checkoutSummary']);

    // Account
    Route::put('account/profile', [StorefrontApiController::class, 'updateProfile']);
    Route::post('account/change-password', [StorefrontApiController::class, 'changePassword']);
    Route::post('account/deactivate', [\App\Http\Controllers\Api\AuthExtendedController::class, 'deactivate']);
    Route::post('auth/token/refresh', [\App\Http\Controllers\Api\AuthExtendedController::class, 'refreshToken']);

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

    // Vendor APIs (register/status open to authed users; rest gated in-controller)
    Route::post('vendor/register', [\App\Http\Controllers\Api\VendorApiController::class, 'register']);
    Route::get('vendor/status', [\App\Http\Controllers\Api\VendorApiController::class, 'status']);
    Route::get('vendor/dashboard', [\App\Http\Controllers\Api\VendorApiController::class, 'dashboard']);
    Route::get('vendor/products', [\App\Http\Controllers\Api\VendorApiController::class, 'products']);
    Route::post('vendor/products', [\App\Http\Controllers\Api\VendorApiController::class, 'storeProduct']);
    Route::get('vendor/products/{productId}', [\App\Http\Controllers\Api\VendorApiController::class, 'showProduct']);
    Route::put('vendor/products/{productId}', [\App\Http\Controllers\Api\VendorApiController::class, 'updateProduct']);
    Route::delete('vendor/products/{productId}', [\App\Http\Controllers\Api\VendorApiController::class, 'destroyProduct']);
    Route::get('vendor/orders', [\App\Http\Controllers\Api\VendorApiController::class, 'orders']);
    Route::get('vendor/profile', [\App\Http\Controllers\Api\VendorApiController::class, 'profile']);
    Route::put('vendor/profile', [\App\Http\Controllers\Api\VendorApiController::class, 'updateProfile']);
});

// AI Admin (admin-only via sanctum)
Route::middleware(['auth:sanctum'])->prefix('admin/ai')->group(function () {
    Route::post('query', function (\Illuminate\Http\Request $request) {
        abort_unless($request->user()->isAdmin(), 403, 'Admin access required.');
        return app(AdminAiAssistantController::class)->query($request, app(\App\Services\AiAdminAssistantService::class));
    });
});

// Admin APIs (sanctum + admin role, enforced in controller guard)
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\Api\AdminApiController::class, 'dashboard']);

    Route::get('vendors', [\App\Http\Controllers\Api\AdminApiController::class, 'vendors']);
    Route::post('vendors/{vendor}/approve', [\App\Http\Controllers\Api\AdminApiController::class, 'approveVendor']);
    Route::post('vendors/{vendor}/suspend', [\App\Http\Controllers\Api\AdminApiController::class, 'suspendVendor']);
    Route::post('vendors/{vendor}/commission', [\App\Http\Controllers\Api\AdminApiController::class, 'commission']);

    Route::get('products', [\App\Http\Controllers\Api\AdminApiController::class, 'products']);
    Route::patch('products/{product}/toggle', [\App\Http\Controllers\Api\AdminApiController::class, 'toggleProduct']);
    Route::delete('products/{product}', [\App\Http\Controllers\Api\AdminApiController::class, 'destroyProduct']);

    Route::get('orders', [\App\Http\Controllers\Api\AdminApiController::class, 'orders']);
    Route::get('orders/{order}', [\App\Http\Controllers\Api\AdminApiController::class, 'showOrder']);
    Route::post('orders/{order}/status', [\App\Http\Controllers\Api\AdminApiController::class, 'orderStatus']);

    Route::get('returns', [\App\Http\Controllers\Api\AdminApiController::class, 'returns']);
    Route::post('returns/{returnRequest}/action', [\App\Http\Controllers\Api\AdminApiController::class, 'returnAction']);

    Route::get('users', [\App\Http\Controllers\Api\AdminApiController::class, 'users']);
    Route::post('users/{user}/toggle-status', [\App\Http\Controllers\Api\AdminApiController::class, 'toggleUser']);
});
