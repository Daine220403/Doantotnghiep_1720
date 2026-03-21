<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\orders;
use App\Models\bookings;
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
            ->where('status', 'paid')
            ->count();
        $totalSpent = orders::where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('total_amount');

        $upcomingBookings = bookings::with(['departure.tour', 'order'])
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->whereIn('status', ['pending', 'confirmed', 'paid']);
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

        return view('dashboard', compact(
            'user',
            'totalOrders',
            'totalPaidOrders',
            'totalSpent',
            'upcomingBookings',
            'upcomingCount',
            'recentBookings'
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

        return view('bookings.show', [
            'user' => $user,
            'booking' => $booking,
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
