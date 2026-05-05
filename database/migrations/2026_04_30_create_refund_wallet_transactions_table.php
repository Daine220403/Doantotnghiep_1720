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
        Schema::create('refund_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique()->comment('Mã giao dịch duy nhất');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('refund_wallet_id')->constrained('refund_wallets')->onDelete('cascade');
            
            // Thông tin giao dịch
            $table->enum('type', ['refund', 'withdrawal', 'adjustment'])->comment('Loại giao dịch: nhận hoàn tiền, rút tiền, điều chỉnh');
            $table->decimal('amount', 15, 2)->comment('Số tiền giao dịch');
            $table->decimal('balance_before', 15, 2)->comment('Số dư trước giao dịch');
            $table->decimal('balance_after', 15, 2)->comment('Số dư sau giao dịch');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed')->comment('Trạng thái giao dịch');
            
            // Liên kết
            $table->foreignId('refund_request_id')->nullable()->constrained('refund_requests')->nullOnDelete();
            $table->string('related_type')->nullable()->comment('Loại liên kết: refund_request, withdrawal_request, etc.');
            $table->unsignedBigInteger('related_id')->nullable()->comment('ID của liên kết');
            
            // Mô tả
            $table->text('description')->nullable()->comment('Mô tả giao dịch');
            $table->json('metadata')->nullable()->comment('Dữ liệu bổ sung');
            
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_wallet_transactions');
    }
};
