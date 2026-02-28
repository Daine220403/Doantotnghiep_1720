<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();

            $table->enum('item_type', ['tour', 'service', 'addon'])->index();
            $table->unsignedBigInteger('item_id')->index();

            $table->string('item_name', 255);
            $table->integer('qty')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')
                  ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};
