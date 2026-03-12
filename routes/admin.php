<?php
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\toursController;

Route::prefix('/admin')->middleware(['auth','admin'])->group(function () {
    Route::get('/', function () {
        return view('admin.index');
    })->name('admin.index');

    Route::prefix('/tours')->group(function () {
        Route::get('/', [toursController::class, 'index'])->name('admin.mana-tour.index');
        Route::get('/create', [toursController::class, 'create'])->name('admin.mana-tour.create');
        Route::post('/store', [toursController::class, 'store'])->name('admin.mana-tour.store');
        Route::get('/{id}/edit', [toursController::class, 'edit'])->name('admin.mana-tour.edit');
        Route::put('/{id}', [toursController::class, 'update'])->name('admin.mana-tour.update');
        Route::delete('/{id}', [toursController::class, 'destroy'])->name('admin.mana-tour.destroy');
    });
    // tours/{slug}
    // Route::get('/tours/{slug}', function () {
    //     return view('tour_show');
    // })->name('tours.show');
    // Route::get('/danh-sach-tour', function () {
    //     return view('admin.mana_tour.index');
    // })->name('admin.mana-tour');
});