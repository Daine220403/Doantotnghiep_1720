<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tour_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('departure_id')->index();
            $table->unsignedBigInteger('guide_id')->index();
            $table->unsignedBigInteger('assigned_by')->nullable()->index();

            $table->enum('status', ['assigned', 'active', 'completed', 'cancelled'])
                  ->default('assigned')->index();

            $table->timestamps();

            $table->foreign('departure_id')->references('id')->on('tour_departures')
                  ->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreign('guide_id')->references('id')->on('users')
                  ->restrictOnDelete()->cascadeOnUpdate();

            $table->foreign('assigned_by')->references('id')->on('users')
                  ->nullOnDelete()->cascadeOnUpdate();

            // Optional: 1 phiên tour - 1 guide (nếu muốn)
            // $table->unique(['departure_id', 'guide_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_assignments');
    }
};
