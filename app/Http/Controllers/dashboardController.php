<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\orders;
use App\Models\bookings;
use App\Models\payments;
use Illuminate\Support\Facades\DB;

class dashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('signin');
        }

        $totalOrders = orders::where('user_id', $user->id)->count();
        $totalPaidOrders = orders::where('user_id', $user->id)
            ->whereIn('status', ['partial_paid', 'paid'])
            ->count();
        $totalSpent = orders::where('user_id', $user->id)
            ->whereIn('status', ['partial_paid', 'paid'])
            ->sum('total_amount');

        $upcomingBookings = bookings::with(['departure.tour', 'order'])
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->whereIn('status', ['pending', 'partial_paid', 'paid']);
            })
            ->whereHas('departure', function ($q) {
                $q->where('start_date', '>=', now()->toDateString());
            })
            ->get()
            ->sortBy(function ($booking) {
                return optional($booking->departure)->start_date;
            })
            ->values();

        $upcomingCount = $upcomingBookings->count();

        $recentBookings = bookings::with(['departure.tour', 'order'])
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->latest()
            ->limit(10)
            ->get();

        // Tính tổng tiền đã thanh toán cho từng đơn để hiển thị nhanh trên dashboard
        $orderIds = $upcomingBookings->pluck('order.id')
            ->merge($recentBookings->pluck('order.id'))
            ->filter()
            ->unique()
            ->values();

        $paidByOrder = [];

        if ($orderIds->isNotEmpty()) {
            $payments = payments::whereIn('order_id', $orderIds)
                ->where('status', 'success')
                ->get()
                ->groupBy('order_id');

            foreach ($payments as $orderId => $group) {
                $paidByOrder[$orderId] = $group->sum('amount');
            }
        }

        return view('dashboard', compact(
            'user',
            'totalOrders',
            'totalPaidOrders',
            'totalSpent',
            'upcomingBookings',
            'upcomingCount',
            'recentBookings',
            'paidByOrder'
        ));
    }

    public function showBooking($bookingId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('signin');
        }

        $booking = bookings::with(['order', 'departure.tour', 'passengers'])
            ->where('id', $bookingId)
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->firstOrFail();

        $order = $booking->order;

        $paidAmount = 0;
        $remainingAmount = 0;
        $isFullyPaid = false;
        $isDeposit = false;
        $lastPaymentType = null;

        if ($order) {
            $paymentsQuery = payments::where('order_id', $order->id)
                ->where('status', 'success');

            $paidAmount = (clone $paymentsQuery)->sum('amount');

            $latestPayment = (clone $paymentsQuery)
                ->orderByDesc('paid_at')
                ->orderByDesc('id')
                ->first();

            if ($latestPayment) {
                $lastPaymentType = $latestPayment->payment_type;
            }

            $remainingAmount = max(($order->total_amount ?? 0) - $paidAmount, 0);
            $isFullyPaid = $paidAmount >= (($order->total_amount ?? 0) - 1);
            $isDeposit = ($lastPaymentType === 'deposit') || ($paidAmount > 0 && !$isFullyPaid);
        }

        return view('bookings.show', [
            'user' => $user,
            'booking' => $booking,
            'paidAmount' => $paidAmount,
            'remainingAmount' => $remainingAmount,
            'isFullyPaid' => $isFullyPaid,
            'isDeposit' => $isDeposit,
            'lastPaymentType' => $lastPaymentType,
        ]);
    }

    public function cancelBooking($bookingId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('signin');
        }

        $booking = bookings::with(['order', 'departure', 'passengers'])
            ->where('id', $bookingId)
            ->firstOrFail();

        $order = $booking->order;

        if (!$order || $order->user_id !== $user->id) {
            return redirect()->route('dashboard')->with('error', 'Bạn không có quyền với đơn này');
        }

        if (!in_array($order->status, ['pending', 'failed']) || $booking->status !== 'pending') {
            return redirect()->route('dashboard')
                ->with('error', 'Đơn không hợp lệ để hủy');
        }

        DB::transaction(function () use ($booking, $order) { // đảm bảo tính toàn vẹn dữ liệu khi cập nhật nhiều bảng
            $order->status = 'cancelled';
            $order->save();

            $booking->status = 'cancelled';
            $booking->save();

            $departure = $booking->departure;
            if ($departure) {
                $slots = $booking->passengers->count();
                if ($slots > 0) {
                    $newBooked = max(0, (int) $departure->capacity_booked - $slots);
                    $departure->capacity_booked = $newBooked;
                    $departure->save();
                }
            }
        });

        return redirect()->route('dashboard')->with('success', 'Đơn đã được hủy thành công');
    }
}
