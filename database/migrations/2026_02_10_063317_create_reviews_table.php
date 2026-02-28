<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tour_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('booking_id')->nullable()->index();

            $table->tinyInteger('rating')->unsigned(); // 1-5 (validate in app)
            $table->text('content')->nullable();

            $table->enum('status', ['pending', 'approved', 'hidden'])
                  ->default('pending')->index();

            $table->timestamps();

            $table->foreign('tour_id')->references('id')->on('tours')
                  ->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreign('user_id')->references('id')->on('users')
                  ->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreign('booking_id')->references('id')->on('bookings')
                  ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
