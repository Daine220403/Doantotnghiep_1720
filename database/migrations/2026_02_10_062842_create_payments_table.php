<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();

            $table->string('payment_code', 50)->unique();
            $table->string('method', 50)->index();
            $table->decimal('amount', 12, 2)->default(0);

            $table->enum('status', ['pending', 'success', 'failed', 'cancelled', 'refunded'])
                  ->default('pending')->index();

            $table->timestamp('paid_at')->nullable()->index();
            $table->string('transaction_ref', 255)->nullable()->index();
            $table->json('raw_response')->nullable();

            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')
                  ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
