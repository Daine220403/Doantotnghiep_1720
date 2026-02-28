<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();

            $table->enum('tour_type', ['domestic', 'international'])->index();

            $table->text('summary')->nullable();
            $table->longText('description')->nullable();

            $table->integer('duration_days')->default(0);
            $table->integer('duration_nights')->default(0);

            $table->string('departure_location', 150)->nullable();
            $table->string('destination_text', 255)->nullable();

            $table->decimal('base_price_from', 12, 2)->default(0);

            $table->enum('status', ['draft', 'published', 'hidden'])->default('draft')->index();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')
                  ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
