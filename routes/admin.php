<?php
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\toursController;

Route::prefix('/admin')->middleware(['auth','admin'])->group(function () {
    Route::get('/', function () {
        return view('admin.index');
    })->name('admin.index');

    Route::prefix('/tours')->group(function () {
        Route::get('/', [toursController::class, 'index'])->name('admin.mana-tour.index'); // danh sách tour || done
        Route::get('/create', [toursController::class, 'create'])->name('admin.mana-tour.create'); // form tạo tour || done
        Route::post('/store', [toursController::class, 'store'])->name('admin.mana-tour.store'); // xử lý lưu tour mới || done
        Route::put('/{id}', [toursController::class, 'update'])->name('admin.mana-tour.update'); // xử lý cập nhật tour || done
        Route::delete('/{id}', [toursController::class, 'destroy'])->name('admin.mana-tour.destroy'); // xử lý xóa tour || 
    });
    // tours/{slug}
    // Route::get('/tours/{slug}', function () {
    //     return view('tour_show');
    // })->name('tours.show');
    // Route::get('/danh-sach-tour', function () {
    //     return view('admin.mana_tour.index');
    // })->name('admin.mana-tour');
});