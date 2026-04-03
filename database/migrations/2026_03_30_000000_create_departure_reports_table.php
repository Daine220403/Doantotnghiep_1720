<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('departure_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('departure_id');
            $table->unsignedBigInteger('guide_id');

            $table->string('summary')->nullable();
            $table->text('general_evaluation')->nullable();
            $table->longText('incidents')->nullable();
            $table->longText('itinerary_notes')->nullable();
            $table->decimal('extra_cost_total', 15, 2)->default(0);
            $table->longText('customer_feedback')->nullable();
            $table->longText('guide_suggestion')->nullable();
            $table->string('status')->default('draft');
            $table->longText('manager_note')->nullable();

            $table->timestamps();

            $table->foreign('departure_id')
                ->references('id')->on('tour_departures')
                ->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreign('guide_id')
                ->references('id')->on('users')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departure_reports');
    }
};
