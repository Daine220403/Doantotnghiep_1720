<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('/')->group(function () {
    Route::get('/', function () {
        return view('index');
    })->name('home');
    Route::get('/tours', function () {
        return view('tours');
    })->name('tours');
    Route::get('/dang-nhap', function () {
        return view('signin');
    })->name('signin');
    Route::get('/dang-ky', function () {
        return view('signup');
    })->name('signup');
    // tours/{slug}
    Route::get('/tours/{slug}', function () {
        return view('tour_show');
    })->name('tours.show');
}); 

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
