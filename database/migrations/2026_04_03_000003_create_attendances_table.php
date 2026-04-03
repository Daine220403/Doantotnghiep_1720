<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('work_schedule_id')->nullable()->constrained('work_schedules')->cascadeOnUpdate()->nullOnDelete();
            $table->date('work_date');
            $table->dateTime('check_in_time')->nullable();
            $table->dateTime('check_out_time')->nullable();
            $table->enum('status', ['present', 'late', 'absent', 'on_leave', 'remote'])->default('present')->index();
            $table->enum('source', ['system', 'manual'])->default('system');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['staff_id', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
