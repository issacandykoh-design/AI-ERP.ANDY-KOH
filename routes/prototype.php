<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Prototype Routes - Hardcoded UI/UX Design
|--------------------------------------------------------------------------
|
| These routes serve hardcoded prototype pages for design review.
| No backend functionality - pure UI/UX mockups.
|
*/

Route::prefix('prototype')->name('prototype.')->group(function () {
    
    // Marketplace Public Pages
    Route::prefix('marketplace')->name('marketplace.')->group(function () {
        Route::get('/', function () {
            return view('prototype.marketplace.index');
        })->name('index');
        
        Route::get('/products', function () {
            return view('prototype.marketplace.products.index');
        })->name('products.index');
        
        Route::get('/products/{id}', function ($id) {
            return view('prototype.marketplace.products.show', ['id' => $id]);
        })->name('products.show');
        
        Route::get('/merchants', function () {
            return view('prototype.marketplace.merchants.index');
        })->name('merchants.index');
        
        Route::get('/merchants/{id}', function ($id) {
            return view('prototype.marketplace.merchants.show', ['id' => $id]);
        })->name('merchants.show');
        
        Route::get('/cart', function () {
            return view('prototype.marketplace.cart');
        })->name('cart');
        
        Route::get('/checkout', function () {
            return view('prototype.marketplace.checkout');
        })->name('checkout');
        
        // Additional prototype pages
        Route::get('/order-management', function () {
            return view('prototype.buyer.orders.index');
        })->name('order-management');
        
        Route::get('/pricing-comparison', function () {
            return view('marketplace.prototype.pricing-comparison');
        })->name('pricing-comparison');
        
        Route::get('/delivery-options', function () {
            return view('marketplace.prototype.delivery-options');
        })->name('delivery-options');
        
        Route::get('/ai-recipe', function () {
            return view('marketplace.prototype.ai-recipe-generation');
        })->name('ai-recipe');
    });
    
    // Buyer Dashboard Pages
    Route::prefix('buyer')->name('buyer.')->group(function () {
        Route::get('/dashboard', function () {
            return view('prototype.buyer.dashboard');
        })->name('dashboard');
        
        Route::get('/orders', function () {
            return view('prototype.buyer.orders.index');
        })->name('orders.index');
        
        Route::get('/orders/{id}', function ($id) {
            return view('prototype.buyer.orders.show', ['id' => $id]);
        })->name('orders.show');
        
        Route::get('/account', function () {
            return view('prototype.buyer.account');
        })->name('account');
    });
    
    // Merchant Dashboard Pages
    Route::prefix('merchant')->name('merchant.')->group(function () {
        Route::get('/dashboard', function () {
            return view('prototype.merchant.dashboard');
        })->name('dashboard');
        
        Route::get('/products', function () {
            return view('prototype.merchant.products.index');
        })->name('products.index');
        
        Route::get('/products/create', function () {
            return view('prototype.merchant.products.create');
        })->name('products.create');
        
        Route::get('/orders', function () {
            return view('prototype.merchant.orders.index');
        })->name('orders.index');
        
        Route::get('/orders/{id}', function ($id) {
            return view('prototype.merchant.orders.show', ['id' => $id]);
        })->name('orders.show');
    });
});

