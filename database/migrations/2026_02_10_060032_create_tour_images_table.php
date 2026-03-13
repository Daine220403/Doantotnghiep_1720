<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tour_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tour_id')->index();
            $table->string('url', 255)->nullable();
            $table->integer('sort_order')->default(0)->index();

            $table->foreign('tour_id')->references('id')->on('tours')
                  ->cascadeOnDelete()->cascadeOnUpdate();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_images');
    }
};
