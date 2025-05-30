<?php

use App\Http\Controllers\ClientAuth\ClientAuthenticatedSessionController;
use App\Http\Controllers\ClientAuth\ClientRegisterController;
use App\Http\Controllers\ClientAuth\ClientProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('guest:client')->group(function () {
    Route::get('client/login', [ClientAuthenticatedSessionController::class, 'create'])
        ->name('client.login');

    Route::post('client/login', [ClientAuthenticatedSessionController::class, 'store']);

    Route::get('client/register', [ClientRegisterController::class, 'create'])
        ->name('client.register');

    Route::post('client/register', [ClientRegisterController::class, 'store']);
});

Route::middleware('auth:client')->group(function () {
    Route::post('client/logout', [ClientAuthenticatedSessionController::class, 'destroy'])
        ->name('client.logout');
    
    Route::get('client/dashboard', function () {
        return Inertia::render('client/Dashboard');
    })->name('client.dashboard');
    
    Route::get('client/cart', function () {
        return Inertia::render('client/Cart');
    })->name('client.cart');
    
    Route::get('client/orders', 'App\Http\Controllers\OrderController@clientOrders')
        ->name('client.orders');
    
    Route::post('client/orders', 'App\Http\Controllers\OrderController@clientOrderStore')
        ->name('client.orders.store');
    
    Route::get('client/orders/{order}', 'App\Http\Controllers\OrderController@clientOrderShow')
        ->name('client.orders.show');
    
    // Client profile routes
    Route::get('client/profile', [ClientProfileController::class, 'edit'])
        ->name('client.profile');
    Route::put('client/profile', [ClientProfileController::class, 'update'])
        ->name('client.profile.update');
    Route::put('client/password', [ClientProfileController::class, 'updatePassword'])
        ->name('client.password.update');
});
