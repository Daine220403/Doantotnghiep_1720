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
        Schema::create('refund_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            
            // Số dư ví
            $table->decimal('balance', 15, 2)->default(0)->comment('Số dư hiện tại trong ví');
            $table->decimal('total_received', 15, 2)->default(0)->comment('Tổng tiền đã nhận');
            $table->decimal('total_withdrawn', 15, 2)->default(0)->comment('Tổng tiền đã rút');
            
            // Trạng thái
            $table->enum('status', ['active', 'locked', 'suspended'])->default('active')->comment('Trạng thái ví');
            
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_wallets');
    }
};
