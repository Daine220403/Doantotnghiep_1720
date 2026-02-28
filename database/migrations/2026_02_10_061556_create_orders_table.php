<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 50)->unique();

            $table->unsignedBigInteger('user_id')->nullable()->index(); // guest checkout allowed

            $table->string('contact_name', 150);
            $table->string('contact_phone', 20);
            $table->string('contact_email', 150);

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->enum('status', [
                'draft', 'pending', 'confirmed', 'paid', 'cancelled', 'refunded', 'failed'
            ])->default('pending')->index();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')
                  ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
