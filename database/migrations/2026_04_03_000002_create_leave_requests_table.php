<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('leave_type', ['annual', 'sick', 'unpaid', 'other']);
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending')->index();
            $table->timestamp('approved_at')->nullable();
            $table->text('approved_note')->nullable();
            $table->timestamps();

            $table->index(['staff_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
