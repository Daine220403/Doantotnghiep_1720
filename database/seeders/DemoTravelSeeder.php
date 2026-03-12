<?php

namespace Database\Seeders;

use App\Models\User;
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

            // ===== Tour =====
            $tourId = DB::table('tours')->insertGetId([
                'code' => 'TOUR-DL-3N2D',
                'title' => 'Đà Lạt 3 ngày 2 đêm',
                'slug' => Str::slug('Đà Lạt 3 ngày 2 đêm'),
                'tour_type' => 'domestic',
                'description' => 'Tour tham quan Đà Lạt với lịch trình hấp dẫn, nghỉ khách sạn trung tâm.',
                'duration_days' => 3,
                'duration_nights' => 2,
                'departure_location' => 'TP.HCM',
                'destination_text' => 'Đà Lạt',
                'base_price_from' => 3500000,
                'status' => 'published',
                'created_by' => $tourManagerId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('tour_images')->insert([
                [
                    'tour_id' => $tourId,
                    'url' => 'image/tour/dalat-1.jpg',
                    'sort_order' => 1,
                ],
                [
                    'tour_id' => $tourId,
                    'url' => 'image/tour/dalat-2.jpg',
                    'sort_order' => 2,
                ],
            ]);

            DB::table('tour_itineraries')->insert([
                [
                    'tour_id' => $tourId,
                    'day_no' => 1,
                    'title' => 'TP.HCM - Đà Lạt',
                    'content' => 'Khởi hành từ TP.HCM, tham quan các điểm nổi bật trên đường đi.',
                ],
                [
                    'tour_id' => $tourId,
                    'day_no' => 2,
                    'title' => 'Khám phá Đà Lạt',
                    'content' => 'Tham quan LangBiang, vườn hoa, chợ đêm Đà Lạt.',
                ],
                [
                    'tour_id' => $tourId,
                    'day_no' => 3,
                    'title' => 'Đà Lạt - TP.HCM',
                    'content' => 'Trả phòng, mua sắm đặc sản và trở về TP.HCM.',
                ],
            ]);

            $departureId = DB::table('tour_departures')->insertGetId([
                'tour_id' => $tourId,
                'start_date' => Carbon::now()->addDays(10)->toDateString(),
                'end_date' => Carbon::now()->addDays(12)->toDateString(),
                'meeting_point' => 'Bến xe Miền Đông - Cổng chính',
                'capacity_total' => 30,
                'capacity_booked' => 2,
                'price_adult' => 3500000,
                'price_child' => 2500000,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ===== Partner Services =====
            $hotelServiceId = DB::table('partner_services')->insertGetId([
                'partner_id' => $hotelPartnerId,
                'name' => 'Phòng khách sạn tiêu chuẩn 2 người',
                'service_type' => 'hotel_room',
                'description' => 'Phòng tiêu chuẩn, 2 khách/phòng, gần trung tâm Đà Lạt',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $transportServiceId = DB::table('partner_services')->insertGetId([
                'partner_id' => $transportPartnerId,
                'name' => 'Xe du lịch 29 chỗ',
                'service_type' => 'vehicle',
                'description' => 'Xe đưa đón đoàn khách theo lịch trình tour',
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
                'discount_total' => 500000,
                'total_amount' => 6500000,
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
                    'special_request' => null,
                ],
                [
                    'booking_id' => $bookingId,
                    'full_name' => 'Nguyễn Thị Lan',
                    'gender' => 'female',
                    'dob' => '2001-09-20',
                    'id_no' => '079200000002',
                    'passenger_type' => 'adult',
                    'special_request' => 'Ăn chay',
                ],
            ]);

            DB::table('payments')->insert([
                'order_id' => $orderId,
                'payment_code' => 'PAY000001',
                'method' => 'vnpay',
                'amount' => 6500000,
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
        });
    }
}