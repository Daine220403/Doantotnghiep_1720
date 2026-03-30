<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tour_departures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tour_id')->index();

            $table->date('start_date')->index();
            $table->date('end_date')->index();

            $table->string('meeting_point', 255)->nullable();

            $table->integer('capacity_total')->default(0);
            $table->integer('capacity_booked')->default(0);

            $table->decimal('price_adult', 12, 2)->default(0); // Giá áp dụng cho khách từ 12 tuổi trở lên
            $table->decimal('price_child', 12, 2)->default(0); // Giá áp dụng cho khách từ 5 đến dưới 11 tuổi (có thể là giá riêng hoặc miễn phí)            
            $table->decimal('price_infant', 12, 2)->default(0); // Giá áp dụng cho khách từ 2 đến dưới 5 tuổi (có thể là giá riêng hoặc miễn phí)  
            $table->decimal('price_youth', 12, 2)->default(0); // Giá áp dụng cho khách dưới 2 tuổi (có thể là giá riêng hoặc miễn phí)

            // Phụ thu phòng đơn cho mỗi lịch khởi hành (nếu khách chọn ở 1 người/1 phòng)
            $table->decimal('single_room_surcharge', 12, 2)->default(0);
     
            $table->enum('status', ['draft', 'open', 'closed', 'sold_out', 'cancelled','confirmed', 'running','completed'])
                  ->default('draft')->index();

            $table->timestamps();

            $table->foreign('tour_id')->references('id')->on('tours')
                  ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_departures');
    }
};
