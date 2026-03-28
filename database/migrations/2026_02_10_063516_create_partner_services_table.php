<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('partner_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partner_id')->index();

            $table->string('name', 255);
            $table->string('service_type', 50)->index();
            $table->text('description')->nullable();

            $table->decimal('unit_price', 12, 2)->default(0);

            $table->enum('status', ['active', 'inactive'])
                  ->default('active')->index();

            // Theo spec: không có timestamps
            $table->foreign('partner_id')->references('id')->on('partners')
                  ->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_services');
    }
};
