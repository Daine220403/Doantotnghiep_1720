<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TourPolicySeeder extends Seeder
{
    public function run(): void
    {
        $tourId = DB::table('tours')->first()->id ?? 1;

        DB::table('tour_policies')->insert([
            // ===== Bao gồm =====
            [
                'tour_id' => $tourId,
                'type' => 'include',
                'content' => 'Xe du lịch đời mới đưa đón theo chương trình',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tour_id' => $tourId,
                'type' => 'include',
                'content' => 'Khách sạn tiêu chuẩn 3 sao (2 khách/phòng)',
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tour_id' => $tourId,
                'type' => 'include',
                'content' => 'Các bữa ăn theo chương trình',
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tour_id' => $tourId,
                'type' => 'include',
                'content' => 'Hướng dẫn viên chuyên nghiệp',
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ===== Không bao gồm =====
            [
                'tour_id' => $tourId,
                'type' => 'exclude',
                'content' => 'Chi phí cá nhân ngoài chương trình',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tour_id' => $tourId,
                'type' => 'exclude',
                'content' => 'Đồ uống trong các bữa ăn',
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tour_id' => $tourId,
                'type' => 'exclude',
                'content' => 'Thuế VAT',
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}