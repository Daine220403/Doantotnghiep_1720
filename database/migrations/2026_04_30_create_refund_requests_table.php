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
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();
            $table->string('refund_code')->unique()->comment('Mã hoàn tiền duy nhất');
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Thông tin hoàn tiền
            $table->decimal('refund_amount', 15, 2)->comment('Số tiền cần hoàn lại');
            $table->enum('status', ['pending', 'approved', 'rejected', 'refunded', 'failed'])->default('pending')->comment('pending, approved, rejected, refunded, failed');
            $table->enum('refund_method', ['wallet', 'vnpay'])->default('vnpay')->comment('Phương thức hoàn tiền');
            
            // Thông tin thanh toán VNPay
            $table->string('vnpay_payment_code')->nullable()->comment('Mã giao dịch VNPay hoàn tiền');
            $table->string('vnpay_transaction_no')->nullable()->comment('Mã giao dịch VNPay từ server');
            $table->string('vnpay_bank_tran_no')->nullable()->comment('Mã giao dịch ngân hàng');
            $table->json('vnpay_response')->nullable()->comment('Response từ VNPay');
            
            // Thông tin admin
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->comment('Admin duyệt yêu cầu');
            $table->timestamp('approved_at')->nullable()->comment('Thời gian duyệt');
            $table->text('approval_note')->nullable()->comment('Ghi chú khi duyệt');
            
            // Thông tin từ chối
            $table->text('rejection_reason')->nullable()->comment('Lý do từ chối');
            $table->timestamp('rejected_at')->nullable()->comment('Thời gian từ chối');
            
            // Thông tin hoàn tiền thành công
            $table->timestamp('refunded_at')->nullable()->comment('Thời gian hoàn tiền thành công');
            
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('booking_id');
            $table->index('order_id');
            $table->index('status');
            $table->index('refund_method');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_requests');
    }
};
