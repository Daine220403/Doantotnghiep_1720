<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\signin_upController;
use App\Http\Controllers\indexController;
use App\Http\Controllers\paymentController;
use App\Http\Controllers\dashboardController;

Route::prefix('/')->group(function () {
    Route::get('/', [indexController::class, 'index'])->name('home'); 

    Route::get('/dang-nhap', [signin_upController::class, 'signin'])->name('signin');
    Route::post('/dang-nhap-store', [signin_upController::class, 'signinStore'])->name('signin.store');
    Route::get('/dang-xuat', [signin_upController::class, 'logout'])->name('logout');

    Route::get('/dang-ky', [signin_upController::class, 'signup'])->name('signup');
    Route::post('/dang-ky-store', [signin_upController::class, 'signupStore'])->name('signup.store');


    route::prefix('/tours')->group(function () {
        Route::get('/', [indexController::class, 'tours'])->name('tours'); 
        Route::get('/{slug}', [indexController::class, 'show'])->name('tours.show');
        // Checkout + thanh toán VNPay
        Route::post('/checkout', [paymentController::class, 'vnpay_payment'])->name('tours.checkout');
    });
});

Route::post('/vnpay_payment', [paymentController::class, 'vnpay_payment'])->name('vnpay_payment');

// URL VNPay redirect về sau khi thanh toán
Route::get('/vnpay/return', [paymentController::class, 'vnpayReturn'])->name('vnpay.return');

Route::get('/dashboard', [dashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
