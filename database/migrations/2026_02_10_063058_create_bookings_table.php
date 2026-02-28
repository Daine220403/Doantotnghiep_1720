<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('departure_id')->index();

            $table->text('note')->nullable();

            $table->enum('status', [
                'pending', 'confirmed', 'paid', 'cancelled', 'completed'
            ])->default('pending')->index();

            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')
                  ->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreign('departure_id')->references('id')->on('tour_departures')
                  ->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
