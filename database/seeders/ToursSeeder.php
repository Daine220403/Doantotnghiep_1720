<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ToursSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Lấy tour manager ID nếu user đã tồn tại, ngược lại dùng null
            $tourManagerId = DB::table('users')
                ->where('role', 'tour_manager')
                ->value('id');

            // ===== Main Tour with Full Details =====
            $mainTourId = DB::table('tours')->insertGetId([
                'code' => 'TOUR-DL-3N2D',
                'title' => 'Đà Lạt 3 ngày 2 đêm',
                'slug' => Str::slug('Đà Lạt 3 ngày 2 đêm'),
                'tour_type' => 'domestic',
                'description' => 'Tour tham quan Đà Lạt với lịch trình hấp dẫn, nghỉ khách sạn trung tâm.',
                'duration_days' => 3,
                'duration_nights' => 2,
                'departure_location' => 'TP.HCM',
                'destination_text' => 'Đà Lạt',
                'transport' => 'bus',
                'base_price_from' => 3500000,
                'status' => 'published',
                'created_by' => $tourManagerId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Images for main tour
            DB::table('tour_images')->insert([
                [
                    'tour_id' => $mainTourId,
                    'url' => 'image/tour/dalat-1.jpg',
                    'sort_order' => 1,
                ],
                [
                    'tour_id' => $mainTourId,
                    'url' => 'image/tour/dalat-2.jpg',
                    'sort_order' => 2,
                ],
            ]);

            // Itineraries for main tour
            DB::table('tour_itineraries')->insert([
                [
                    'tour_id' => $mainTourId,
                    'day_no' => 1,
                    'title' => 'TP.HCM - Đà Lạt',
                    'content' => 'Khởi hành từ TP.HCM, tham quan các điểm nổi bật trên đường đi.',
                ],
                [
                    'tour_id' => $mainTourId,
                    'day_no' => 2,
                    'title' => 'Khám phá Đà Lạt',
                    'content' => 'Tham quan LangBiang, vườn hoa, chợ đêm Đà Lạt.',
                ],
                [
                    'tour_id' => $mainTourId,
                    'day_no' => 3,
                    'title' => 'Đà Lạt - TP.HCM',
                    'content' => 'Trả phòng, mua sắm đặc sản và trở về TP.HCM.',
                ],
            ]);

            // Departures for main tour
            DB::table('tour_departures')->insert([
                [
                    'tour_id' => $mainTourId,
                    'start_date' => Carbon::now()->addDays(10)->toDateString(),
                    'end_date' => Carbon::now()->addDays(12)->toDateString(),
                    'meeting_point' => 'Bến xe Miền Đông - Cổng chính',
                    'capacity_total' => 30,
                    'capacity_booked' => 2,
                    'price_adult' => 3500000,
                    'price_child' => 2500000,
                    'price_infant' => 1500000,
                    'price_youth' => 500000,
                    'single_room_surcharge' => 1000000,
                    'status' => 'open',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tour_id' => $mainTourId,
                    'start_date' => Carbon::now()->addDays(20)->toDateString(),
                    'end_date' => Carbon::now()->addDays(22)->toDateString(),
                    'meeting_point' => 'Bến xe Miền Đông - Cổng chính',
                    'capacity_total' => 30,
                    'capacity_booked' => 5,
                    'price_adult' => 3590000,
                    'price_child' => 2590000,
                    'price_infant' => 1590000,
                    'price_youth' => 550000,
                    'single_room_surcharge' => 1000000,
                    'status' => 'open',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tour_id' => $mainTourId,
                    'start_date' => Carbon::now()->addDays(30)->toDateString(),
                    'end_date' => Carbon::now()->addDays(32)->toDateString(),
                    'meeting_point' => 'Bến xe Miền Đông - Cổng chính',
                    'capacity_total' => 30,
                    'capacity_booked' => 10,
                    'price_adult' => 3690000,
                    'price_child' => 2690000,
                    'price_infant' => 1690000,
                    'price_youth' => 600000,
                    'single_room_surcharge' => 1100000,
                    'status' => 'open',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tour_id' => $mainTourId,
                    'start_date' => Carbon::now()->addDays(40)->toDateString(),
                    'end_date' => Carbon::now()->addDays(42)->toDateString(),
                    'meeting_point' => 'Bến xe Miền Đông - Cổng chính',
                    'capacity_total' => 30,
                    'capacity_booked' => 0,
                    'price_adult' => 3790000,
                    'price_child' => 2790000,
                    'price_infant' => 1790000,
                    'price_youth' => 650000,
                    'single_room_surcharge' => 1100000,
                    'status' => 'open',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tour_id' => $mainTourId,
                    'start_date' => Carbon::now()->addDays(50)->toDateString(),
                    'end_date' => Carbon::now()->addDays(52)->toDateString(),
                    'meeting_point' => 'Bến xe Miền Đông - Cổng chính',
                    'capacity_total' => 30,
                    'capacity_booked' => 20,
                    'price_adult' => 3890000,
                    'price_child' => 2890000,
                    'price_infant' => 1890000,
                    'price_youth' => 700000,
                    'single_room_surcharge' => 1200000,
                    'status' => 'open',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // ===== Additional Tours with Images =====
            $additionalTours = [
                [
                    'code' => 'TOUR-DL-2N1D',
                    'title' => 'Đà Lạt 2 ngày 1 đêm',
                    'tour_type' => 'domestic',
                    'description' => 'Tour Đà Lạt nghỉ ngắn ngày, phù hợp cuối tuần.',
                    'duration_days' => 2,
                    'duration_nights' => 1,
                    'departure_location' => 'TP.HCM',
                    'destination_text' => 'Đà Lạt',
                    'transport' => 'bus',
                    'base_price_from' => 2800000,
                    'images' => [
                        ['url' => 'image/tour/dalat4.jpg', 'sort_order' => 1],
                        ['url' => 'image/tour/dalat3.jpg', 'sort_order' => 2],
                        ['url' => 'image/tour/dalat5.jpg', 'sort_order' => 3],
                    ],
                ],
                [
                    'code' => 'TOUR-PQ-3N2D',
                    'title' => 'Phú Quốc 3 ngày 2 đêm',
                    'tour_type' => 'domestic',
                    'description' => 'Tour biển đảo Phú Quốc, tham quan VinWonders, Safari.',
                    'duration_days' => 3,
                    'duration_nights' => 2,
                    'departure_location' => 'TP.HCM',
                    'destination_text' => 'Phú Quốc',
                    'transport' => 'plane',
                    'base_price_from' => 5200000,
                    'images' => [
                        ['url' => 'image/tour/phuquoc1.jpg', 'sort_order' => 1],
                        ['url' => 'image/tour/phuquoc2.jpg', 'sort_order' => 2],
                        ['url' => 'image/tour/phuquoc3.jpg', 'sort_order' => 3],
                        ['url' => 'image/tour/phuquoc4.jpg', 'sort_order' => 4],
                    ],
                ],
                [
                    'code' => 'TOUR-NT-3N2D',
                    'title' => 'Nha Trang 3 ngày 2 đêm',
                    'tour_type' => 'domestic',
                    'description' => 'Tour nghỉ dưỡng Nha Trang, tắm biển và thưởng thức hải sản.',
                    'duration_days' => 3,
                    'duration_nights' => 2,
                    'departure_location' => 'TP.HCM',
                    'destination_text' => 'Nha Trang',
                    'transport' => 'bus',
                    'base_price_from' => 3200000,
                    'images' => [
                        ['url' => 'image/tour/nhatrang.jpg', 'sort_order' => 1],
                        ['url' => 'image/tour/nhatrang1.jpg', 'sort_order' => 2], 
                        ['url' => 'image/tour/nhatrang2.jpg', 'sort_order' => 3], 
                    ],
                ],
                [
                    'code' => 'TOUR-SP-4N3D',
                    'title' => 'Hà Nội - Sapa 4 ngày 3 đêm',
                    'tour_type' => 'domestic',
                    'description' => 'Khám phá núi rừng Tây Bắc, Fansipan, bản Cát Cát.',
                    'duration_days' => 4,
                    'duration_nights' => 3,
                    'departure_location' => 'Hà Nội',
                    'destination_text' => 'Sapa',
                    'transport' => 'train',
                    'base_price_from' => 4800000,
                    'images' => [
                        ['url' => 'image/tour/hanoi.jpg', 'sort_order' => 1],
                        ['url' => 'image/tour/hanoi1.jpg', 'sort_order' => 2],
                        ['url' => 'image/tour/hanoi2.jpg', 'sort_order' => 3],
                        ['url' => 'image/tour/hanoi3.jpg', 'sort_order' => 4],
                    ],
                ],
                [
                    'code' => 'TOUR-DN-3N2D',
                    'title' => 'Đà Nẵng - Hội An 3 ngày 2 đêm',
                    'tour_type' => 'domestic',
                    'description' => 'Check-in Bà Nà Hills, phố cổ Hội An về đêm.',
                    'duration_days' => 3,
                    'duration_nights' => 2,
                    'departure_location' => 'TP.HCM',
                    'destination_text' => 'Đà Nẵng, Hội An',
                    'transport' => 'plane',
                    'base_price_from' => 5600000,
                    'images' => [
                        ['url' => 'image/tour/danang.jpg', 'sort_order' => 1],
                        ['url' => 'image/tour/danang1.jpg', 'sort_order' => 2],
                        ['url' => 'image/tour/danang2.jpg', 'sort_order' => 3],
                        ['url' => 'image/tour/danang3.jpg', 'sort_order' => 4],
                    ],
                ],
                [
                    'code' => 'TOUR-MT-2N1D',
                    'title' => 'Miền Tây sông nước 2 ngày 1 đêm',
                    'tour_type' => 'domestic',
                    'description' => 'Trải nghiệm chợ nổi, miệt vườn trái cây miền Tây.',
                    'duration_days' => 2,
                    'duration_nights' => 1,
                    'departure_location' => 'TP.HCM',
                    'destination_text' => 'Mỹ Tho, Cần Thơ',
                    'transport' => 'bus',
                    'base_price_from' => 1900000,
                    'images' => [
                        ['url' => 'image/tour/mytho.jpg', 'sort_order' => 1],
                        ['url' => 'image/tour/mytho1.jpg', 'sort_order' => 2],
                        ['url' => 'image/tour/mytho2.jpg', 'sort_order' => 3],
                        ['url' => 'image/tour/mytho3.jpg', 'sort_order' => 4],
                    ],
                ],
                [
                    'code' => 'TOUR-SG-MY-4N3D',
                    'title' => 'Singapore - Malaysia 4 ngày 3 đêm',
                    'tour_type' => 'international',
                    'description' => 'Khám phá Garden by the Bay, Malacca, Kuala Lumpur.',
                    'duration_days' => 4,
                    'duration_nights' => 3,
                    'departure_location' => 'TP.HCM',
                    'destination_text' => 'Singapore, Malaysia',
                    'transport' => 'plane',
                    'base_price_from' => 12900000,
                    'images' => [
                        ['url' => 'image/tour/sgp.jpg', 'sort_order' => 1],
                        ['url' => 'image/tour/sgp1.jpg', 'sort_order' => 2],
                        ['url' => 'image/tour/sgp2.jpg', 'sort_order' => 3],
                        ['url' => 'image/tour/sgp3.jpg', 'sort_order' => 4],
                    ],
                ],
                [
                    'code' => 'TOUR-TH-5N4D',
                    'title' => 'Bangkok - Pattaya 5 ngày 4 đêm',
                    'tour_type' => 'international',
                    'description' => 'Du lịch Thái Lan, mua sắm và thưởng thức ẩm thực đường phố.',
                    'duration_days' => 5,
                    'duration_nights' => 4,
                    'departure_location' => 'TP.HCM',
                    'destination_text' => 'Bangkok, Pattaya',
                    'transport' => 'plane',
                    'base_price_from' => 9900000,
                    'images' => [
                        ['url' => 'image/tour/bk.jpg', 'sort_order' => 1],
                        ['url' => 'image/tour/bk1.jpg', 'sort_order' => 2],
                        ['url' => 'image/tour/bk2.jpg', 'sort_order' => 3],
                        ['url' => 'image/tour/bk3.jpg', 'sort_order' => 4],
                    ],
                ],
                [
                    'code' => 'TOUR-JP-5N4D',
                    'title' => 'Nhật Bản mùa hoa anh đào 5 ngày 4 đêm',
                    'tour_type' => 'international',
                    'description' => 'Tham quan Tokyo, Núi Phú Sĩ, trải nghiệm văn hóa Nhật Bản.',
                    'duration_days' => 5,
                    'duration_nights' => 4,
                    'departure_location' => 'TP.HCM',
                    'destination_text' => 'Tokyo, Núi Phú Sĩ',
                    'transport' => 'plane',
                    'base_price_from' => 28900000,
                    'images' => [
                        ['url' => 'image/tour/nb.jpg', 'sort_order' => 1],
                        ['url' => 'image/tour/nb1.jpg', 'sort_order' => 2],
                        ['url' => 'image/tour/nb2.jpg', 'sort_order' => 3],
                        ['url' => 'image/tour/nb3.jpg', 'sort_order' => 4],
                    ],
                ],
                [
                    'code' => 'TOUR-KR-5N4D',
                    'title' => 'Hàn Quốc Seoul - Nami 5 ngày 4 đêm',
                    'tour_type' => 'international',
                    'description' => 'Khám phá Seoul, đảo Nami, mặc Hanbok truyền thống.',
                    'duration_days' => 5,
                    'duration_nights' => 4,
                    'departure_location' => 'TP.HCM',
                    'destination_text' => 'Seoul, Nami',
                    'transport' => 'plane',
                    'base_price_from' => 21500000,
                    'images' => [
                        ['url' => 'image/tour/hq1.jpg', 'sort_order' => 1],
                        ['url' => 'image/tour/hq2.jpg', 'sort_order' => 2],
                        ['url' => 'image/tour/hq3.jpg', 'sort_order' => 3],
                        ['url' => 'image/tour/hq4.jpg', 'sort_order' => 4],
                    ],
                ],
            ];

            foreach ($additionalTours as $tour) {
                $tourId = DB::table('tours')->insertGetId([
                    'code' => $tour['code'],
                    'title' => $tour['title'],
                    'slug' => Str::slug($tour['title']),
                    'tour_type' => $tour['tour_type'],
                    'description' => $tour['description'],
                    'duration_days' => $tour['duration_days'],
                    'duration_nights' => $tour['duration_nights'],
                    'departure_location' => $tour['departure_location'],
                    'destination_text' => $tour['destination_text'],
                    'transport' => $tour['transport'],
                    'base_price_from' => $tour['base_price_from'],
                    'status' => 'published',
                    'created_by' => $tourManagerId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Add images for this tour
                if (isset($tour['images']) && !empty($tour['images'])) {
                    $images = [];
                    foreach ($tour['images'] as $image) {
                        $images[] = [
                            'tour_id' => $tourId,
                            'url' => $image['url'],
                            'sort_order' => $image['sort_order'],
                        ];
                    }
                    DB::table('tour_images')->insert($images);
                }

                // Add departure for this tour
                DB::table('tour_departures')->insert([
                    'tour_id' => $tourId,
                    'start_date' => Carbon::now()->addDays(15)->toDateString(),
                    'end_date' => Carbon::now()->addDays(15 + $tour['duration_days'] - 1)->toDateString(),
                    'meeting_point' => $tour['departure_location'],
                    'capacity_total' => 30,
                    'capacity_booked' => 0,
                    'price_adult' => $tour['base_price_from'],
                    'price_child' => $tour['base_price_from'] * 0.7,
                    'price_infant' => $tour['base_price_from'] * 0.4,
                    'price_youth' => 500000,
                    'single_room_surcharge' => 1000000,
                    'status' => 'open',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
