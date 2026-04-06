<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->date('work_date');
            $table->enum('shift_type', ['morning', 'afternoon', 'fullday', 'night']);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('status', ['assigned', 'cancelled'])->default('assigned')->index();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['staff_id', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
