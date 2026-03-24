<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\toursController;
use App\Http\Controllers\admin\bookingController;
use App\Http\Controllers\admin\StaffBookingController;
use App\Http\Controllers\admin\TourAssignmentController;
use App\Http\Controllers\admin\guideController;

Route::prefix('/admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', function () {
        return view('admin.index');
    })->name('admin.index');

    // Quản lý Hướng dẫn viên
    Route::get('/guides', [guideController::class, 'index'])->name('admin.mana-guide.index');
    Route::get('/guides/{guide}/assign-tours', [guideController::class, 'showTours'])->name('admin.mana-guide.tours');
    Route::get('/guides/{guide}/tours/{tour}/departures', [guideController::class, 'showDepartures'])->name('admin.mana-guide.tour-departures');
    // Phân công Hướng dẫn viên cho lịch khởi hành
    Route::post('/departures/{departure}/assign-guide', [TourAssignmentController::class, 'assign'])
        ->name('admin.departures.assign-guide');
        
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

    // Chức năng dành cho nhân viên: xem tour khách đã đặt, đặt/hủy tour giúp khách
    Route::prefix('/staff-booking')->group(function () {
        // Danh sách tour có booking của khách
        Route::get('/tours', [StaffBookingController::class, 'index'])->name('admin.staff-booking.tours');

        // Chi tiết 1 tour: lịch khởi hành + danh sách booking/khách
        Route::get('/tours/{id}', [StaffBookingController::class, 'showTour'])->name('admin.staff-booking.tours.show');

        // Form đặt tour cho khách theo 1 lịch khởi hành
        Route::get('/departures/{departure}/create', [StaffBookingController::class, 'create'])->name('admin.staff-booking.create');

        // Lưu booking do nhân viên tạo hộ khách
        Route::post('/bookings', [StaffBookingController::class, 'store'])->name('admin.staff-booking.store');

        // Hủy booking cho khách
        Route::post('/bookings/{id}/cancel', [StaffBookingController::class, 'cancel'])->name('admin.staff-booking.cancel');
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
