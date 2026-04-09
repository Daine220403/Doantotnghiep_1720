<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->date('report_date');
            $table->string('title', 255);
            $table->text('content');
            $table->string('file_path', 255)->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('submitted')->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['staff_id', 'report_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_reports');
    }
};
