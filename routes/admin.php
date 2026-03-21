<?php
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\toursController;
use App\Http\Controllers\admin\bookingController;

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

    Route::prefix('/bookings')->group(function () {
        Route::get('/', [bookingController::class, 'index'])->name('admin.mana-booking.index'); // danh sách booking
        Route::get('/{id}', [bookingController::class, 'show'])->name('admin.mana-booking.show'); // xem chi tiết booking
        Route::put('/{id}/status', [bookingController::class, 'updateStatus'])->name('admin.mana-booking.update-status'); // cập nhật trạng thái booking
    });

    // Danh sách khách hàng theo tour
    Route::prefix('/customer-tours')->group(function () {
        Route::get('/', [bookingController::class, 'tourCustomerIndex'])->name('admin.customer-tour.index'); // danh sách tour
        Route::get('/{id}', [bookingController::class, 'tourCustomerShow'])->name('admin.customer-tour.show'); // chi tiết khách theo tour
    });

    // Chốt đoàn cho 1 lịch khởi hành
    Route::post('/departures/{departure}/confirm', [toursController::class, 'confirmDeparture'])
        ->name('admin.departures.confirm');

});