<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MenuController::class, 'index'])->name('home');
Route::get('/menu/{menu}', [MenuController::class, 'show'])->name('menus.show');
Route::middleware('auth')->get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.submit');
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'store'])->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', [AuthController::class, 'adminLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.submit');

    Route::middleware('admin')->group(function (): void {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/menus', [AdminController::class, 'menus'])->name('menus');
        Route::post('/menus', [AdminController::class, 'storeMenu'])->name('menus.store');
        Route::put('/menus/{menu}', [AdminController::class, 'updateMenu'])->name('menus.update');
        Route::delete('/menus/{menu}', [AdminController::class, 'deleteMenu'])->name('menus.delete');
        Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
        Route::patch('/orders/{order}/status', [AdminController::class, 'updateOrderStatus'])->name('orders.status');
    });
});

Route::prefix('api')->name('api.')->group(function (): void {
    Route::get('/products', [MenuController::class, 'apiIndex'])->name('products');
    Route::get('/categories', [MenuController::class, 'categories'])->name('categories');
    Route::get('/session', [AuthController::class, 'sessionInfo'])->name('session');

    Route::get('/cart', [CartController::class, 'show'])->name('cart');
    Route::post('/cart/{menu}', [CartController::class, 'add'])->name('cart.add');
    Route::put('/cart/{menu}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{menu}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

    Route::middleware('auth')->group(function (): void {
        Route::get('/orders', [OrderController::class, 'history'])->name('orders');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    });
});

Route::get('/cart', fn () => redirect(route('home').'#keranjang'))->name('cart.page');
