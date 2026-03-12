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
        Schema::create('tour_policies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tour_id')->constrained('tours')->cascadeOnDelete();

            $table->enum('type', ['include', 'exclude']);
            $table->string('content');

            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_policies');
    }
};
