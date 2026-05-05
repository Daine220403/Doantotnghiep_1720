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
     * Hoàn tiền thử nghiệm (ghi chép)
     */
    public function testProcess($refundId)
    {
        try {
            $refund = RefundRequest::findOrFail($refundId);
            
            if ($refund->status !== 'approved') {
                return redirect()->back()->with('error', 'Yêu cầu hoàn tiền chưa được duyệt');
            }

            // Xử lý hoàn tiền thành công (bỏ qua VNPay trong test)
            $this->refundService->processRefundSuccess($refundId, [
                'vnp_TxnRef' => 'TEST-' . time(),
                'vnp_TransactionNo' => rand(100000, 999999),
                'test_mode' => true,
            ]);

            return redirect()->back()->with('success', 'Hoàn tiền test thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }
}
