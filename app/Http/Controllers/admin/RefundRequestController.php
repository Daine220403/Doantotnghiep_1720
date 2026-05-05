<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use App\Models\bookings;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RefundRequestController extends Controller
{
    protected $refundService;

    public function __construct()
    {
        $this->refundService = new RefundService();
    }

    /**
     * Danh sách các yêu cầu hoàn tiền
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        $query = RefundRequest::with(['booking.departure.tour', 'order', 'user', 'approvedBy']);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $refunds = $query->latest()->paginate(20);

        // Thống kê
        $stats = $this->refundService->getRefundStatistics();

        return view('admin.refund-requests.index', compact('refunds', 'status', 'stats'));
    }

    /**
     * Chi tiết yêu cầu hoàn tiền
     */
    public function show($refundId)
    {
        $refund = RefundRequest::with([
            'booking.departure.tour',
            'booking.passengers',
            'order.orderDetails',
            'user',
            'approvedBy',
            'walletTransaction'
        ])->findOrFail($refundId);

        // Lấy thông tin thanh toán
        $payments = $refund->order->payments ?? collect([]);

        return view('admin.refund-requests.show', compact('refund', 'payments'));
    }

    /**
     * Duyệt yêu cầu hoàn tiền
     */
    public function approve(Request $request, $refundId)
    {
        try {
            $admin = Auth::user();
            $note = $request->get('note', '');

            $refund = $this->refundService->approveRefundRequest($refundId, $admin->id, $note);

            return redirect()->back()->with('success', 'Đã duyệt yêu cầu hoàn tiền. Chuẩn bị chuyển tiền qua VNPay...');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Từ chối yêu cầu hoàn tiền
     */
    public function reject(Request $request, $refundId)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $refund = $this->refundService->rejectRefundRequest($refundId, $request->reason);

            return redirect()->back()->with('success', 'Đã từ chối yêu cầu hoàn tiền');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý hoàn tiền qua VNPay
     * (Có thể tạo job/command để chạy định kỳ)
     */
    public function processRefund($refundId)
    {
        try {
            $refund = RefundRequest::findOrFail($refundId);

            if ($refund->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Yêu cầu hoàn tiền chưa được duyệt',
                ], 400);
            }

            // Gọi VNPay API để chuyển tiền
            $vnpayResult = $this->processVNPayRefund($refund);

            if ($vnpayResult['success']) {
                // Cập nhật status thành refunded
                $this->refundService->processRefundSuccess($refundId, $vnpayResult['data']);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Hoàn tiền thành công và đã cộng vào ví của khách',
                    'data' => $vnpayResult['data'],
                ]);
            } else {
                $this->refundService->processRefundFailed($refundId, $vnpayResult['message']);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi: ' . $vnpayResult['message'],
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Chuyển tiền hoàn lại qua VNPay API
     * Lưu ý: Đây là giả lập, thực tế cần integrate thực sự với VNPay
     */
    private function processVNPayRefund($refund)
    {
        try {
            // TODO: Integrate thực sự với VNPay API
            // Tạm thời giả lập thành công
            
            // Thực tế, bạn cần gọi VNPay refund API như sau:
            // - Tìm giao dịch thanh toán gốc từ payments table
            // - Gọi VNPay API để hoàn tiền
            // - Lấy transaction reference từ response

            $payments = $refund->order->payments()
                ->where('method', 'vnpay')
                ->where('status', 'success')
                ->latest()
                ->first();

            if (!$payments) {
                throw new \Exception('Không tìm thấy giao dịch thanh toán VNPay để hoàn tiền');
            }

            // Giả lập response từ VNPay (thực tế cần implement thực sự)
            $vnpayResponse = [
                'vnp_TxnRef' => 'REF-' . time(),
                'vnp_TransactionNo' => rand(100000, 999999),
                'vnp_BankTranNo' => 'BANK-' . time(),
                'vnp_ResponseCode' => '00',
                'vnp_Message' => 'Approve',
            ];

            return [
                'success' => true,
                'data' => $vnpayResponse,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Hoàn tiền qua VNPay (similar to booking payment flow)
     */
    public function testProcess(Request $request, $refundId)
    {
        try {
            $refund = RefundRequest::findOrFail($refundId);
            
            if ($refund->status !== 'approved') {
                return redirect()->back()->with('error', 'Yêu cầu hoàn tiền chưa được duyệt');
            }

            $refundAmount = $refund->refund_amount;
            if ($refundAmount <= 0) {
                return redirect()->back()->with('error', 'Số tiền hoàn tiền không hợp lệ');
            }

            // VNPay configuration
            $vnp_TmnCode = "KF3WIF8C";
            $vnp_HashSecret = "KCA2KNYAWFXVEUQ4DNH4PQC801ABVGTB";
            $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
            $vnp_Returnurl = route('vnpay.refund.return');
            
            // Create a payment record for refund tracking
            $payment = \App\Models\payments::create([
                'order_id' => $refund->order_id,
                'payment_code' => 'REF' . now()->format('YmdHis') . rand(100, 999),
                'payment_type' => 'refund',
                'method' => 'vnpay',
                'amount' => $refundAmount,
                'status' => 'pending',
                'transaction_ref' => null,
                'raw_response' => null,
            ]);

            // Store refund_id reference in raw_response for later lookup
            $payment->update(['raw_response' => ['refund_id' => $refundId]]);

            $vnp_TxnRef = $payment->payment_code;
            $vnp_OrderInfo = "Hoàn tiền tour - Mã yêu cầu: " . $refund->refund_code;
            $vnp_OrderType = "Refund";
            $vnp_Amount = (int)($refundAmount * 100);
            $vnp_Locale = "vn";
            $vnp_BankCode = "NCB";
            $vnp_IpAddr = $request->ip();

            $inputData = [
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef,
            ];

            if (isset($vnp_BankCode) && $vnp_BankCode != "") {
                $inputData['vnp_BankCode'] = $vnp_BankCode;
            }

            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $vnp_Url = $vnp_Url . "?" . $query;
            if (isset($vnp_HashSecret)) {
                $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
                $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
            }

            // Redirect to VNPay payment gateway
            return redirect()->away($vnp_Url);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Handle VNPay refund return callback
     */
    public function vnpayRefundReturn(Request $request)
    {
        $vnp_HashSecret = "KCA2KNYAWFXVEUQ4DNH4PQC801ABVGTB";

        $inputData = $request->all();
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? null;
        
        $vnpData = [];
        foreach ($inputData as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $vnpData[$key] = $value;
            }
        }

        unset($vnpData['vnp_SecureHash']);
        ksort($vnpData);
        
        $hashData = '';
        $i = 0;
        foreach ($vnpData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        
        $txnRef = $vnpData['vnp_TxnRef'] ?? null;
        $responseCode = $vnpData['vnp_ResponseCode'] ?? null;
        $vnpAmount = isset($vnpData['vnp_Amount']) ? ((int)$vnpData['vnp_Amount']) / 100 : null;
        $vnpTransactionNo = $vnpData['vnp_TransactionNo'] ?? ($vnpData['vnp_BankTranNo'] ?? null);

        if ($secureHash === $vnp_SecureHash && $txnRef) {
            // Find payment by payment_code
            $payment = \App\Models\payments::where('payment_code', $txnRef)
                ->where('method', 'vnpay')
                ->where('payment_type', 'refund')
                ->latest()
                ->first();

            if ($payment && $payment->raw_response) {
                $rawResponse = is_array($payment->raw_response) ? $payment->raw_response : json_decode($payment->raw_response, true);
                $refundId = $rawResponse['refund_id'] ?? null;

                if ($refundId) {
                    $refund = RefundRequest::find($refundId);

                    if ($refund && $responseCode === '00') {
                        // Payment successful
                        $payment->status = 'success';
                        $payment->amount = $vnpAmount ?? $payment->amount;
                        $payment->paid_at = now();
                        $payment->transaction_ref = $vnpTransactionNo;
                        $payment->raw_response = $vnpData;
                        $payment->save();

                        // Process refund success
                        $this->refundService->processRefundSuccess($refundId, [
                            'vnp_TxnRef' => $txnRef,
                            'vnp_TransactionNo' => $vnpTransactionNo,
                            'vnp_BankTranNo' => $vnpData['vnp_BankTranNo'] ?? null,
                        ]);

                        return redirect()->route('admin.refund-requests.index')->with('success', 'Hoàn tiền VNPay thành công!');
                    } else {
                        // Payment failed
                        $payment->status = 'failed';
                        $payment->amount = $vnpAmount ?? $payment->amount;
                        $payment->transaction_ref = $vnpTransactionNo;
                        $payment->raw_response = $vnpData;
                        $payment->save();

                        $this->refundService->processRefundFailed($refundId, 'VNPay response code: ' . $responseCode);

                        return redirect()->route('admin.refund-requests.show', $refundId)
                            ->with('error', 'Hoàn tiền VNPay không thành công (mã lỗi: ' . $responseCode . ')');
                    }
                }
            }
        }

        return redirect()->route('admin.refund-requests.index')->with('error', 'Lỗi xác thực VNPay hoặc dữ liệu không hợp lệ');
    }

    // public function testProcess($refundId)
    // {
    //     try {
    //         $refund = RefundRequest::findOrFail($refundId);
            
    //         if ($refund->status !== 'approved') {
    //             return redirect()->back()->with('error', 'Yêu cầu hoàn tiền chưa được duyệt');
    //         }

    //         // Xử lý hoàn tiền thành công (bỏ qua VNPay trong test)
    //         $this->refundService->processRefundSuccess($refundId, [
    //             'vnp_TxnRef' => 'TEST-' . time(),
    //             'vnp_TransactionNo' => rand(100000, 999999),
    //             'test_mode' => true,
    //         ]);

    //         return redirect()->back()->with('success', 'Hoàn tiền test thành công!');
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'Lỗi: ' . $e->getMessage());
    //     }
    // }
}
