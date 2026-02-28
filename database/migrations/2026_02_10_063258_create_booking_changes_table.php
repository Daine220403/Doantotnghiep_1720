<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_changes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->index();

            $table->enum('type', ['cancel', 'change'])->index();
            $table->text('reason')->nullable();

            $table->enum('request_status', ['pending', 'approved', 'rejected', 'processed'])
                  ->default('pending')->index();

            $table->unsignedBigInteger('handled_by')->nullable()->index();
            $table->timestamp('handled_at')->nullable()->index();

            $table->timestamps();

            $table->foreign('booking_id')->references('id')->on('bookings')
                  ->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreign('handled_by')->references('id')->on('users')
                  ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_changes');
    }
};
