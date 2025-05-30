<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Store front routes (accessible to everyone)
Route::get('/store', function () {
    return Inertia::render('client/Store');
})->name('store');

Route::get('/store/products', [ProductController::class, 'index'])->name('store.products');
Route::get('/store/products/{id}', [ProductController::class, 'show'])->name('store.products.show');

// Admin Dashboard routes (for users/admins)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    
    // Admin routes for inventory management
    Route::resource('products', ProductController::class);
    Route::resource('clients', 'App\Http\Controllers\ClientController');
    Route::resource('stocks', 'App\Http\Controllers\StockController');
    Route::resource('stock-movements', 'App\Http\Controllers\StockMovementController');
    Route::resource('orders', OrderController::class);
    
    // Additional admin routes
    Route::post('/stocks/{id}/adjust', 'App\Http\Controllers\StockController@adjustStock')->name('stocks.adjust');
    Route::get('/orders/statistics', [OrderController::class, 'getStatistics'])->name('orders.statistics');
});

// Client Dashboard routes (for clients/customers)
Route::middleware(['auth:client'])->group(function () {
    Route::get('/client/dashboard', function () {
        return Inertia::render('client/Dashboard');
    })->name('client.dashboard');
    
    // Shopping cart routes
    Route::get('/client/cart', function () {
        return Inertia::render('client/Cart');
    })->name('client.cart');
    
    // Order management for clients
    Route::get('/client/orders', [OrderController::class, 'clientOrders'])->name('client.orders');
    Route::get('/client/orders/{id}', [OrderController::class, 'clientOrderShow'])->name('client.orders.show');
    Route::post('/client/orders', [OrderController::class, 'clientOrderStore'])->name('client.orders.store');
    
    // Client profile management
    Route::get('/client/profile', function () {
        return Inertia::render('client/Profile');
    })->name('client.profile');
    
    Route::get('/client/addresses', 'App\Http\Controllers\AddressController@clientAddresses')->name('client.addresses');
    Route::post('/client/addresses', 'App\Http\Controllers\AddressController@clientAddressStore')->name('client.addresses.store');
    Route::put('/client/addresses/{id}', 'App\Http\Controllers\AddressController@clientAddressUpdate')->name('client.addresses.update');
    Route::delete('/client/addresses/{id}', 'App\Http\Controllers\AddressController@clientAddressDestroy')->name('client.addresses.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/client.php';
