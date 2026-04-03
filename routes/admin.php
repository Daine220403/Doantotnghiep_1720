<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\toursController;
use App\Http\Controllers\admin\bookingController;
use App\Http\Controllers\admin\StaffBookingController;
use App\Http\Controllers\admin\TourAssignmentController;
use App\Http\Controllers\admin\guideController;
use App\Http\Controllers\admin\partnerController;
use App\Http\Controllers\admin\TourOperationController;
use App\Http\Controllers\admin\StaffManagementController;
use App\Http\Controllers\paymentController;
use App\Http\Controllers\guide\TourGuideController;

Route::prefix('/admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', function () {
        return view('admin.index');
    })->name('admin.index');

    // Hồ sơ cá nhân cho admin
    Route::get('/profile', [ProfileController::class, 'editAdmin'])->name('admin.profile.edit');

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

        // Xem / cập nhật lại thông tin booking & hành khách
        Route::get('/bookings/{booking}/edit', [StaffBookingController::class, 'edit'])->name('admin.staff-booking.edit');
        Route::put('/bookings/{booking}', [StaffBookingController::class, 'update'])->name('admin.staff-booking.update');

        // Ghi nhận thanh toán offline: cọc 30% và thanh toán đủ
        Route::post('/bookings/{booking}/deposit', [StaffBookingController::class, 'deposit'])->name('admin.staff-booking.deposit');
        Route::post('/bookings/{booking}/pay-full', [StaffBookingController::class, 'payFull'])->name('admin.staff-booking.pay-full');

        // Thanh toán VNPay cho booking do nhân viên thao tác (khách quét trực tiếp)
        Route::post('/bookings/{booking}/deposit-vnpay', [paymentController::class, 'staffDepositBooking'])
            ->name('admin.staff-booking.deposit-vnpay');
        Route::post('/bookings/{booking}/pay-full-vnpay', [paymentController::class, 'staffPayFullBooking'])
            ->name('admin.staff-booking.pay-full-vnpay');

        // Hủy booking cho khách
        Route::post('/bookings/{id}/cancel', [StaffBookingController::class, 'cancel'])->name('admin.staff-booking.cancel');
    });

    // Danh sách khách hàng theo tour
    Route::prefix('/customer-tours')->group(function () {
        Route::get('/', [bookingController::class, 'tourCustomerIndex'])->name('admin.customer-tour.index'); // danh sách tour
        Route::get('/{id}', [bookingController::class, 'tourCustomerShow'])->name('admin.customer-tour.show'); // chi tiết khách theo tour
    });

    // Theo dõi tour đang chạy (theo từng lịch khởi hành)
    Route::get('/running-tours', [TourOperationController::class, 'runningToursIndex'])
        ->name('admin.running-tours.index');
    Route::get('/running-tours/{departure}', [TourOperationController::class, 'showRunningTour'])
        ->name('admin.running-tours.show');

    // Chốt đoàn cho 1 lịch khởi hành
    Route::post('/departures/{departure}/confirm', [toursController::class, 'confirmDeparture'])
        ->name('admin.departures.confirm');

    // Danh sách tour điều phối (tour đang chạy + đã chốt đoàn)
    Route::get('/coordinated-tours', [TourOperationController::class, 'coordinatedToursIndex'])
        ->name('admin.coordinated-tours.index');

    // Dịch vụ đối tác theo lịch khởi hành (điều phối dịch vụ)
    Route::get('/departures/{departure}/services', [TourOperationController::class, 'servicesIndex'])
        ->name('admin.departures.services.index');
    Route::post('/departures/{departure}/services', [TourOperationController::class, 'servicesStore'])
        ->name('admin.departures.services.store');
    Route::delete('/departures/{departure}/services/{id}', [TourOperationController::class, 'servicesDestroy'])
        ->name('admin.departures.services.destroy');

    // Quản lý đối tác dịch vụ
    Route::prefix('/partners')->group(function () {
        Route::get('/', [partnerController::class, 'index'])->name('admin.mana-partner.index');
        Route::get('/create', [partnerController::class, 'create'])->name('admin.mana-partner.create');
        Route::post('/store', [partnerController::class, 'store'])->name('admin.mana-partner.store');
        Route::get('/{partner}/edit', [partnerController::class, 'edit'])->name('admin.mana-partner.edit');
        Route::put('/{partner}', [partnerController::class, 'update'])->name('admin.mana-partner.update');
        Route::delete('/{partner}', [partnerController::class, 'destroy'])->name('admin.mana-partner.destroy');

        // Dịch vụ của đối tác
        Route::get('/{partner}/services', [partnerController::class, 'services'])->name('admin.mana-partner.services');
        Route::post('/{partner}/services', [partnerController::class, 'storeService'])->name('admin.mana-partner.services.store');
        Route::delete('/services/{service}', [partnerController::class, 'destroyService'])->name('admin.mana-partner.services.destroy');
    });

    // Khu vực dành cho Hướng dẫn viên: xem tour được phân công, chi tiết khách, báo cáo
    Route::prefix('/guide')->middleware(['guide'])->group(function () {
        Route::get('/tours', [TourGuideController::class, 'index'])->name('guide.tours.index');
        Route::get('/departures/{departure}', [TourGuideController::class, 'showDeparture'])->name('guide.departures.show');
        Route::get('/departures/{departure}/report', [TourGuideController::class, 'report'])->name('guide.departures.report');
        Route::post('/departures/{departure}/report', [TourGuideController::class, 'storeReport'])->name('guide.departures.report.store');
    });

    // Quản lý nhân viên (dành cho admin & staff_manager)
    Route::prefix('/hr')->group(function () {
        // Lịch làm việc nhân viên
        Route::get('/schedules', [StaffManagementController::class, 'schedulesIndex'])->name('admin.hr.schedules.index');

        // Đơn nghỉ phép
        Route::get('/leaves', [StaffManagementController::class, 'leavesIndex'])->name('admin.hr.leaves.index');
        Route::post('/leaves/{leave}/approve', [StaffManagementController::class, 'approveLeave'])->name('admin.hr.leaves.approve');
        Route::post('/leaves/{leave}/reject', [StaffManagementController::class, 'rejectLeave'])->name('admin.hr.leaves.reject');

        // Chấm công
        Route::get('/attendances', [StaffManagementController::class, 'attendancesIndex'])->name('admin.hr.attendances.index');
        // Chỉ dùng một endpoint check-in tạo/cập nhật theo staff + ngày
        Route::post('/attendances/check-in', [StaffManagementController::class, 'attendanceCheckInForStaff'])->name('admin.hr.attendances.check-in');
        Route::post('/attendances/{attendance}/check-out', [StaffManagementController::class, 'attendanceCheckOut'])->name('admin.hr.attendances.check-out');

        // Lương
        Route::get('/payrolls', [StaffManagementController::class, 'payrollsIndex'])->name('admin.hr.payrolls.index');

        // Báo cáo công việc
        Route::get('/reports', [StaffManagementController::class, 'reportsIndex'])->name('admin.hr.reports.index');
    });

    // Khu vực nhân viên tự thao tác (xem lịch, xin nghỉ, chấm công, báo cáo của chính mình)
    Route::prefix('/staff-hr')->group(function () {
        Route::get('/schedules', [StaffManagementController::class, 'mySchedules'])->name('admin.staff-hr.schedules.index');

        Route::get('/leaves', [StaffManagementController::class, 'myLeavesIndex'])->name('admin.staff-hr.leaves.index');
        Route::post('/leaves', [StaffManagementController::class, 'myLeavesStore'])->name('admin.staff-hr.leaves.store');

        Route::get('/attendances', [StaffManagementController::class, 'myAttendances'])->name('admin.staff-hr.attendances.index');

        Route::get('/reports', [StaffManagementController::class, 'myReportsIndex'])->name('admin.staff-hr.reports.index');
        Route::post('/reports', [StaffManagementController::class, 'myReportsStore'])->name('admin.staff-hr.reports.store');
    });
});
