<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DemoTravelSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // ===== Partners =====
            $hotelPartnerId = DB::table('partners')->insertGetId([
                'name' => 'Khách sạn Đà Lạt Xanh',
                'type' => 'hotel',
                'phone' => '0909000001',
                'email' => 'hotel@dalatxanh.vn',
                'address' => '12 Trần Phú, Đà Lạt',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $transportPartnerId = DB::table('partners')->insertGetId([
                'name' => 'Nhà xe Cao Nguyên Travel',
                'type' => 'transport',
                'phone' => '0909000002',
                'email' => 'transport@caonguyen.vn',
                'address' => '45 Điện Biên Phủ, TP.HCM',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ===== Users =====
            $adminId = DB::table('users')->insertGetId([
                'name' => 'Admin VieTravel',
                'email' => 'admin@vietravel.local',
                'phone' => '0901111111',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'status' => 'active',
                'partner_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $tourManagerId = DB::table('users')->insertGetId([
                'name' => 'Nguyễn Văn Điều Hành',
                'email' => 'manager@vietravel.local',
                'phone' => '0902222222',
                'password' => Hash::make('12345678'),
                'role' => 'tour_manager',
                'status' => 'active',
                'partner_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $guideId = DB::table('users')->insertGetId([
                'name' => 'Trần Thị Hướng Dẫn',
                'email' => 'guide@vietravel.local',
                'phone' => '0903333333',
                'password' => Hash::make('12345678'),
                'role' => 'tour_guide',
                'status' => 'active',
                'partner_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $customerId = DB::table('users')->insertGetId([
                'name' => 'Lê Minh Khách',
                'email' => 'customer@vietravel.local',
                'phone' => '0904444444',
                'password' => Hash::make('12345678'),
                'role' => 'customer',
                'status' => 'active',
                'partner_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $staffManagerId = DB::table('users')->insertGetId([
                'name' => 'Nguyễn Văn Quản Lý',
                'email' => 'staff_manager@vietravel.local',
                'phone' => '0906666666',
                'password' => Hash::make('12345678'),
                'role' => 'staff_manager',
                'status' => 'active',
                'partner_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $staff1Id = DB::table('users')->insertGetId([
                'name' => 'Nhân viên CSKH 1',
                'email' => 'staff1@vietravel.local',
                'phone' => '0907777777',
                'password' => Hash::make('12345678'),
                'role' => 'staff',
                'status' => 'active',
                'partner_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $staff2Id = DB::table('users')->insertGetId([
                'name' => 'Nhân viên CSKH 2',
                'email' => 'staff2@vietravel.local',
                'phone' => '0908888888',
                'password' => Hash::make('12345678'),
                'role' => 'staff',
                'status' => 'active',
                'partner_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $partnerUserId = DB::table('users')->insertGetId([
                'name' => 'Quản lý khách sạn Đà Lạt Xanh',
                'email' => 'partner_hotel@vietravel.local',
                'phone' => '0905555555',
                'password' => Hash::make('12345678'),
                'role' => 'partner',
                'status' => 'active',
                'partner_id' => $hotelPartnerId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ===== Tours (handled by ToursSeeder) =====
            // Lấy ID của tour chính TOUR-DL-3N2D để sử dụng trong dữ liệu khác
            $tourId = DB::table('tours')
                ->where('code', 'TOUR-DL-3N2D')
                ->value('id') ?? null;

            // Nếu tour chưa được tạo bởi ToursSeeder, sử dụng departure ID mặc định
            if (!$tourId) {
                // Lấy tour đầu tiên hoặc tạo departure mặc định
                $tourId = DB::table('tours')->value('id') ?? 1;
            }

            $departureId = DB::table('tour_departures')
                ->where('tour_id', $tourId)
                ->orderBy('start_date')
                ->value('id') ?? 1;

            // ===== Partner Services =====
            $hotelServiceId = DB::table('partner_services')->insertGetId([
                'partner_id' => $hotelPartnerId,
                'name' => 'Phòng khách sạn tiêu chuẩn 2 người',
                'service_type' => 'hotel_room',
                'description' => 'Phòng tiêu chuẩn, 2 khách/phòng, gần trung tâm Đà Lạt',
                'unit_price' => 500000,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $transportServiceId = DB::table('partner_services')->insertGetId([
                'partner_id' => $transportPartnerId,
                'name' => 'Xe du lịch 29 chỗ',
                'service_type' => 'vehicle',
                'description' => 'Xe đưa đón đoàn khách theo lịch trình tour',
                'unit_price' => 7000000,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('departure_services')->insert([
                [
                    'departure_id' => $departureId,
                    'partner_service_id' => $hotelServiceId,
                    'service_date' => Carbon::now()->addDays(10)->toDateString(),
                    'qty' => 10,
                    'unit_price' => 500000,
                    'total_price' => 5000000,
                    'status' => 'confirmed',
                    'note' => '10 phòng tiêu chuẩn',
                    'confirmed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'departure_id' => $departureId,
                    'partner_service_id' => $transportServiceId,
                    'service_date' => Carbon::now()->addDays(10)->toDateString(),
                    'qty' => 1,
                    'unit_price' => 7000000,
                    'total_price' => 7000000,
                    'status' => 'confirmed',
                    'note' => 'Xe 29 chỗ phục vụ suốt hành trình',
                    'confirmed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // ===== Order =====
            $orderId = DB::table('orders')->insertGetId([
                'order_code' => 'ORD000001',
                'user_id' => $customerId,
                'contact_name' => 'Lê Minh Khách',
                'contact_phone' => '0904444444',
                'contact_email' => 'customer@vietravel.local',
                'subtotal' => 7000000,
                'discount_total' => 0,
                'total_amount' => 7000000,
                'status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('order_details')->insert([
                'order_id' => $orderId,
                'item_type' => 'tour',
                'item_id' => $tourId,
                'item_name' => 'Đà Lạt 3 ngày 2 đêm',
                'qty' => 2,
                'unit_price' => 3500000,
                'line_total' => 7000000,
                'meta' => json_encode([
                    'departure_id' => $departureId,
                    'adult_count' => 2,
                    'child_count' => 0,
                    'infant_count' => 0,
                    'youth_count' => 0,
                    'single_room' => false,
                ]),
                'created_at' => now(),
            ]);

            $bookingId = DB::table('bookings')->insertGetId([
                'order_id' => $orderId,
                'departure_id' => $departureId,
                'note' => 'Khách muốn ngồi gần cửa sổ',
                'status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('booking_passengers')->insert([
                [
                    'booking_id' => $bookingId,
                    'full_name' => 'Lê Minh Khách',
                    'gender' => 'male',
                    'dob' => '2000-05-10',
                    'id_no' => '079200000001',
                    'passenger_type' => 'adult',
                ],
                [
                    'booking_id' => $bookingId,
                    'full_name' => 'Nguyễn Thị Lan',
                    'gender' => 'female',
                    'dob' => '2001-09-20',
                    'id_no' => '079200000002',
                    'passenger_type' => 'adult',
                ],
            ]);

            DB::table('payments')->insert([
                'order_id' => $orderId,
                'payment_code' => 'PAY000001',
                'method' => 'vnpay',
                'amount' => 7000000,
                'status' => 'success',
                'paid_at' => now(),
                'transaction_ref' => 'VNPAY_123456789',
                'raw_response' => json_encode([
                    'gateway' => 'vnpay',
                    'response_code' => '00',
                    'message' => 'Success',
                ]),
                'created_at' => now(),
            ]);

            DB::table('tour_assignments')->insert([
                'departure_id' => $departureId,
                'guide_id' => $guideId,
                'assigned_by' => $tourManagerId,
                'status' => 'assigned',
                'created_at' => now(),
            ]);

            DB::table('reviews')->insert([
                'tour_id' => $tourId,
                'user_id' => $customerId,
                'booking_id' => $bookingId,
                'rating' => 5,
                'content' => 'Tour rất tốt, lịch trình hợp lý, hướng dẫn viên nhiệt tình.',
                'status' => 'approved',
                'created_at' => now(),
            ]);


            DB::table('booking_changes')->insert([
                'booking_id' => $bookingId,
                'type' => 'change',
                'reason' => 'Khách muốn đổi điểm đón sang Nhà Văn Hóa Thanh Niên',
                'request_status' => 'pending',
                'handled_by' => null,
                'handled_at' => null,
                'created_at' => now(),
            ]);


            // ===== HR Demo Data (Departments, Staff, Work Schedule, Leave, Attendance, Payroll, Reports) =====

            // Phòng ban demo (chỉ sử dụng 4 phòng ban chính theo yêu cầu)
            $systemDepartmentId = DB::table('departments')->insertGetId([
                'name' => 'Phòng Quản trị hệ thống',
                'code' => 'SYS',
                'description' => 'Quản lý và vận hành hệ thống phần mềm, hạ tầng.',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $operationDepartmentId = DB::table('departments')->insertGetId([
                'name' => 'Phòng Điều hành tour',
                'code' => 'OPER',
                'description' => 'Bộ phận điều phối tour và lịch khởi hành.',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $csDepartmentId = DB::table('departments')->insertGetId([
                'name' => 'Phòng Chăm sóc khách hàng',
                'code' => 'CSKH',
                'description' => 'Bộ phận chăm sóc khách hàng và xử lý booking.',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $partnerDepartmentId = DB::table('departments')->insertGetId([
                'name' => 'Phòng Đối tác dịch vụ',
                'code' => 'PARTNER',
                'description' => 'Quản lý và làm việc với các đối tác cung cấp dịch vụ.',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Gán phòng ban cho tài khoản demo
            // Admin: Phòng Quản trị hệ thống
            DB::table('users')->where('id', $adminId)->update([
                'department_id' => $systemDepartmentId,
            ]);

            // Quản lý tour & hướng dẫn viên: Điều hành tour
            DB::table('users')->whereIn('id', [$tourManagerId, $guideId])->update([
                'department_id' => $operationDepartmentId,
            ]);

            // Quản lý nhân sự/nhân viên CSKH: CSKH
            DB::table('users')->where('id', $staffManagerId)->update([
                'department_id' => $csDepartmentId,
            ]);

            DB::table('users')->whereIn('id', [$staff1Id, $staff2Id])->update([
                'department_id' => $csDepartmentId,
            ]);

            // Đối tác dịch vụ: Phòng Đối tác dịch vụ
            DB::table('users')->where('id', $partnerUserId)->update([
                'department_id' => $partnerDepartmentId,
            ]);

            $today = Carbon::today();

            // Lịch làm việc cho nhân viên
            $scheduleTodayId = DB::table('work_schedules')->insertGetId([
                'staff_id' => $staff1Id,
                'manager_id' => $staffManagerId,
                'work_date' => $today->toDateString(),
                'shift_type' => 'fullday',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'status' => 'assigned',
                'note' => 'Trực tổng đài và hỗ trợ khách đặt tour.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $scheduleTomorrowId = DB::table('work_schedules')->insertGetId([
                'staff_id' => $staff1Id,
                'manager_id' => $staffManagerId,
                'work_date' => $today->copy()->addDay()->toDateString(),
                'shift_type' => 'morning',
                'start_time' => '08:00:00',
                'end_time' => '12:00:00',
                'status' => 'assigned',
                'note' => 'Ca sáng xử lý đơn hàng online.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('work_schedules')->insert([
                [
                    'staff_id' => $staff2Id,
                    'manager_id' => $staffManagerId,
                    'work_date' => $today->toDateString(),
                    'shift_type' => 'afternoon',
                    'start_time' => '13:30:00',
                    'end_time' => '17:30:00',
                    'status' => 'assigned',
                    'note' => 'Hỗ trợ khách tại quầy giao dịch.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // Đơn nghỉ phép demo
            DB::table('leave_requests')->insert([
                [
                    'staff_id' => $staff1Id,
                    'manager_id' => $staffManagerId,
                    'start_date' => $today->copy()->addDays(3)->toDateString(),
                    'end_date' => $today->copy()->addDays(3)->toDateString(),
                    'leave_type' => 'annual',
                    'reason' => 'Nghỉ phép năm để giải quyết việc gia đình.',
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_note' => 'Đã sắp xếp nhân sự trực thay.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'staff_id' => $staff2Id,
                    'manager_id' => $staffManagerId,
                    'start_date' => $today->copy()->addDays(5)->toDateString(),
                    'end_date' => $today->copy()->addDays(6)->toDateString(),
                    'leave_type' => 'sick',
                    'reason' => 'Xin nghỉ ốm.',
                    'status' => 'pending',
                    'approved_at' => null,
                    'approved_note' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // Chấm công demo
            DB::table('attendances')->insert([
                [
                    'staff_id' => $staff1Id,
                    'work_schedule_id' => $scheduleTodayId,
                    'work_date' => $today->toDateString(),
                    'check_in_time' => $today->copy()->setTime(8, 5, 0),
                    'check_out_time' => $today->copy()->setTime(17, 0, 0),
                    'status' => 'present',
                    'source' => 'system',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'staff_id' => $staff1Id,
                    'work_schedule_id' => $scheduleTomorrowId,
                    'work_date' => $today->copy()->addDay()->toDateString(),
                    'check_in_time' => $today->copy()->addDay()->setTime(8, 20, 0),
                    'check_out_time' => $today->copy()->addDay()->setTime(12, 0, 0),
                    'status' => 'late',
                    'source' => 'manual',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // Kỳ lương demo
            $payrollPeriodId = DB::table('payroll_periods')->insertGetId([
                'start_date' => Carbon::now()->startOfMonth()->toDateString(),
                'end_date' => Carbon::now()->endOfMonth()->toDateString(),
                'status' => 'closed',
                'created_by' => $staffManagerId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('payroll_items')->insert([
                [
                    'payroll_period_id' => $payrollPeriodId,
                    'staff_id' => $staff1Id,
                    'base_salary' => 12000000,
                    'total_working_days' => 26,
                    'total_overtime_hours' => 5,
                    'allowances' => 1500000,
                    'deductions' => 500000,
                    'bonus' => 1000000,
                    'net_salary' => 13000000,
                    'generated_at' => now(),
                    'approved_by' => $staffManagerId,
                    'status' => 'paid',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'payroll_period_id' => $payrollPeriodId,
                    'staff_id' => $staff2Id,
                    'base_salary' => 10000000,
                    'total_working_days' => 24,
                    'total_overtime_hours' => 2,
                    'allowances' => 1000000,
                    'deductions' => 300000,
                    'bonus' => 500000,
                    'net_salary' => 11200000,
                    'generated_at' => now(),
                    'approved_by' => $staffManagerId,
                    'status' => 'confirmed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // Báo cáo công việc demo
            DB::table('work_reports')->insert([
                [
                    'staff_id' => $staff1Id,
                    'manager_id' => $staffManagerId,
                    'report_date' => $today->toDateString(),
                    'title' => 'Báo cáo công việc CSKH ngày ' . $today->format('d/m/Y'),
                    'content' => 'Tiếp nhận và xử lý 25 cuộc gọi, hỗ trợ 5 booking hoàn tất thanh toán.',
                    'status' => 'approved',
                    'approved_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'staff_id' => $staff2Id,
                    'manager_id' => $staffManagerId,
                    'report_date' => $today->copy()->subDay()->toDateString(),
                    'title' => 'Báo cáo công việc quầy giao dịch',
                    'content' => 'Tư vấn trực tiếp cho 12 khách, tạo 4 booking, thu thập 3 phản hồi dịch vụ.',
                    'status' => 'submitted',
                    'approved_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        });
    }
}