<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\signin_upController;

Route::prefix('/')->group(function () {
    Route::get('/', function () {
        return view('index');
    })->name('home');
    Route::get('/tours', function () {
        return view('tours');
    })->name('tours');
    Route::get('/dang-nhap', [signin_upController::class, 'signin'])->name('signin');
    Route::post('/dang-nhap-store', [signin_upController::class, 'signinStore'])->name('signin.store');
    Route::get('/dang-xuat', [signin_upController::class, 'logout'])->name('logout');
    
    Route::get('/dang-ky', [signin_upController::class, 'signup'])->name('signup');
    Route::post('/dang-ky-store', [signin_upController::class, 'signupStore'])->name('signup.store');

    // tours/{slug}
    Route::get('/tours/{slug}', function () {
        return view('tour_show');
    })->name('tours.show');
}); 

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
