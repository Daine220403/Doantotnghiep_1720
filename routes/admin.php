<?php
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


Route::prefix('/admin')->middleware(['auth','admin'])->group(function () {
    Route::get('/', function () {
        return view('admin.index');
    })->name('admin.index');

    // tours/{slug}
    Route::get('/tours/{slug}', function () {
        return view('tour_show');
    })->name('tours.show');
});