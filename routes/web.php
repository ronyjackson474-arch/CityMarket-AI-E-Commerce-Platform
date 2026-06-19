<?php

use App\Http\Controllers\Admin\AiAssistantController as AdminAiController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Storefront\AccountController;
use App\Http\Controllers\Storefront\AiAssistantController;
use App\Http\Controllers\Storefront\AuthController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\ReviewController;
use App\Http\Controllers\Storefront\ShopController;
use App\Http\Controllers\Storefront\WishlistController;
use Illuminate\Support\Facades\Route;

// ===== Storefront (public) =====
Route::get('/', HomeController::class)->name('home');

Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/category/{slug}', [ShopController::class, 'category'])->name('category');
Route::get('/product/{slug}', [ShopController::class, 'product'])->name('product');

// Customer auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'authLogout'])->name('logout');

// Cart (guests and auth)
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');

// Checkout (auth only for placing orders)
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/order/{orderNumber}/confirmation', [CheckoutController::class, 'confirmation'])->name('order.confirmation');

// AI Assistant
Route::get('/assistant', [AiAssistantController::class, 'index'])->name('assistant');
Route::post('/assistant', [AiAssistantController::class, 'search'])->name('assistant.search');

// AI Support Chat
Route::get('/support', function () {
    return view('storefront.support-chat');
})->name('support');

// ===== Storefront (authenticated customer) =====
Route::middleware(['auth'])->group(function () {
    // Account / Orders
    Route::get('/account', [AccountController::class, 'index'])->name('account');
    Route::get('/account/orders', [AccountController::class, 'orders'])->name('account.orders');
    Route::get('/account/orders/{orderNumber}', [AccountController::class, 'orderDetail'])->name('account.order-detail');
    Route::post('/account/orders/{orderNumber}/cancel', [AccountController::class, 'cancelOrder'])->name('account.cancel-order');
    Route::post('/account/orders/{orderNumber}/return', [AccountController::class, 'requestReturn'])->name('account.request-return');

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::post('/wishlist/add', [WishlistController::class, 'add'])->name('wishlist.add');
    Route::post('/wishlist/remove', [WishlistController::class, 'remove'])->name('wishlist.remove');

    // Reviews
    Route::post('/product/{slug}/review', [ReviewController::class, 'store'])->name('review.store');

    // Addresses
    Route::get('/account/addresses', [AccountController::class, 'addresses'])->name('account.addresses');
    Route::post('/account/addresses', [AccountController::class, 'storeAddress'])->name('account.addresses.store');
    Route::post('/account/addresses/{address}/delete', [AccountController::class, 'deleteAddress'])->name('account.addresses.delete');

    // Wallet
    Route::get('/account/wallet', [AccountController::class, 'wallet'])->name('account.wallet');

    // Referrals
    Route::get('/account/referrals', [AccountController::class, 'referrals'])->name('account.referrals');
});

// ===== Admin =====
Route::prefix('admin')->name('admin.')->group(function () {
    // Admin login uses the same /login form but redirects to admin.dashboard after.
    Route::get('/login', function () {
        return view('admin.login');
    })->name('login');

    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AuthController::class, 'authLogout'])->name('logout');

        Route::resource('products', AdminProductController::class)->except('show');
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('brands', BrandController::class)->except('show');
        Route::resource('coupons', CouponController::class)->except('show');

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');

        // Returns management
        Route::get('/returns', [AdminOrderController::class, 'returns'])->name('returns.index');
        Route::post('/returns/{returnRequest}/action', [AdminOrderController::class, 'handleReturn'])->name('returns.action');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // AI Admin Assistant
        Route::get('/ai-assistant', [AdminAiController::class, 'index'])->name('ai-assistant.index');
        Route::post('/ai-assistant/query', [AdminAiController::class, 'query'])->name('ai-assistant.query');
    });
});
