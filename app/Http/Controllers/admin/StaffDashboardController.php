<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\bookings;
use App\Models\orders;
use App\Models\tour_departures;
use App\Models\booking_passengers;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StaffDashboardController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        if (!$user || ($user->role !== 'staff' && $user->role !== 'admin')) {
            abort(403);
        }

        $today = Carbon::today();

        // Booking cần xử lý (pending, partial_paid)
        $bookingsPending = bookings::with(['order', 'departure.tour'])
            ->whereHas('order', function ($q) {
                $q->whereIn('status', ['pending', 'partial_paid']);
            })
            ->latest()
            ->limit(10)
            ->get();

        // Tổng booking cần xử lý
        $totalPendingBookings = bookings::whereHas('order', function ($q) {
            $q->whereIn('status', ['pending', 'partial_paid']);
        })->count();

        // Booking hôm nay
        $todayBookings = bookings::whereDate('created_at', $today)->count();

        // Booking tuần này
        $weekBookings = bookings::whereBetween('created_at', [
            $today->copy()->subDays(6)->startOfDay(),
            $today->copy()->endOfDay()
        ])->count();

        // Tổng doanh thu từ booking đã thanh toán (hôm nay)
        $dailyRevenue = orders::where('status', 'paid')
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        // Doanh thu tuần này
        $weekRevenue = orders::where('status', 'paid')
            ->whereBetween('created_at', [
                $today->copy()->subDays(6)->startOfDay(),
                $today->copy()->endOfDay()
            ])
            ->sum('total_amount');

        // Lịch khởi hành sắp tới
        $upcomingDepartures = tour_departures::with('tour')
            ->whereDate('start_date', '>=', $today)
            ->whereDate('start_date', '<=', $today->copy()->addDays(30))
            ->orderBy('start_date')
            ->limit(10)
            ->get();

        // Khách gần đây
        $recentCustomers = booking_passengers::with(['booking.order'])
            ->latest()
            ->limit(10)
            ->get();

        // Lịch khởi hành cần chốt đoàn
        $needConfirmDepartures = tour_departures::where('status', 'pending')
            ->whereDate('start_date', '<=', $today->copy()->addDays(5))
            ->count();

        // Booking bị hủy gần đây
        $cancelledBookings = bookings::where('status', 'cancelled')
            ->whereDate('created_at', '>=', $today->copy()->subDays(7))
            ->count();

        return view('admin.staff.dashboard', compact(
            'user',
            'bookingsPending',
            'totalPendingBookings',
            'todayBookings',
            'weekBookings',
            'dailyRevenue',
            'weekRevenue',
            'upcomingDepartures',
            'recentCustomers',
            'needConfirmDepartures',
            'cancelledBookings'
        ));
    }
}
