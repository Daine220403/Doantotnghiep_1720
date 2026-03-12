<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tour_itineraries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tour_id')->index();

            $table->integer('day_no')->index();
            $table->string('title', 255);
            $table->longText('content')->nullable();

            $table->foreign('tour_id')->references('id')->on('tours')
                  ->cascadeOnDelete()->cascadeOnUpdate();

            // Optional unique: 1 tour không trùng day_no
            $table->unique(['tour_id', 'day_no']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_itineraries');
    }
};
