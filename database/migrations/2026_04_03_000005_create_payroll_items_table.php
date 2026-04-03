<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->integer('total_working_days')->default(0);
            $table->decimal('total_overtime_hours', 8, 2)->default(0);
            $table->decimal('allowances', 15, 2)->default(0);
            $table->decimal('deductions', 15, 2)->default(0);
            $table->decimal('bonus', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->default(0)->index();
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->enum('status', ['draft', 'confirmed', 'paid'])->default('draft')->index();
            $table->timestamps();

            $table->index(['staff_id', 'payroll_period_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
    }
};
