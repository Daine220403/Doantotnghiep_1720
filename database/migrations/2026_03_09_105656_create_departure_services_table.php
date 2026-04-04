<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departure_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departure_id')->constrained('tour_departures')->cascadeOnDelete();
            $table->foreignId('partner_service_id')->constrained('partner_services')->cascadeOnDelete();
            $table->date('service_date')->nullable();
            $table->date('service_start_date')->nullable();
            $table->date('service_end_date')->nullable();
            $table->integer('qty')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->text('note')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->boolean('cancel_requested')->default(false);
            $table->timestamp('cancel_requested_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departure_services');
    }
};
