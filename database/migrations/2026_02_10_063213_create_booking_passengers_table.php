<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_passengers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->index();

            $table->string('full_name', 150);
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->index();
            $table->date('dob')->nullable();

            $table->string('id_no', 50)->nullable()->index();

            $table->enum('passenger_type', ['adult', 'child', 'infant', 'youth'])->index();

            $table->boolean('single_room')->default(false);
            $table->decimal('single_room_surcharge', 12, 2)->default(0); // Lưu phụ thu phòng đơn tại thời điểm đặt
            $table->timestamps();
            $table->foreign('booking_id')->references('id')->on('bookings')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_passengers');
    }
};
