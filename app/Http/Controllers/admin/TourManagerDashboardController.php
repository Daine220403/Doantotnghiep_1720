<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tours;
use App\Models\tour_departures;
use App\Models\bookings;
use App\Models\orders;
use App\Models\tour_assignments;
use App\Models\User;
use App\Models\departure_services;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TourManagerDashboardController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        if (!$user || ($user->role !== 'tour_manager' && $user->role !== 'admin')) {
            abort(403);
        }

        $today = Carbon::today();

        // Tổng số tour
        $totalTours = Tours::count();

        // Tổng lịch khởi hành
        $totalDepartures = tour_departures::count();

        // Lịch khởi hành sắp tới (30 ngày)
        $upcomingDeparturesCount = tour_departures::whereDate('start_date', '>=', $today)
            ->whereDate('start_date', '<=', $today->copy()->addDays(30))
            ->count();

        // Lịch khởi hành đang chạy
        $runningDeparturesCount = tour_departures::where('status', 'running')
            ->count();

        // Lịch khởi hành cần xác nhận
        $needConfirmDepartures = tour_departures::where('status', 'pending')
            ->whereDate('start_date', '<=', $today->copy()->addDays(5))
            ->count();

        // Doanh thu tháng này
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();
        $monthlyRevenue = orders::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereIn('status', ['paid', 'completed'])
            ->sum('total_amount');

        // Doanh thu hôm nay
        $dailyRevenue = orders::whereDate('created_at', $today)
            ->whereIn('status', ['paid', 'completed'])
            ->sum('total_amount');

        // Booking tháng này
        $monthlyBookings = bookings::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

        // Lịch khởi hành sắp tới
        $upcomingDepartures = tour_departures::with(['tour', 'assignment.guide'])
            ->whereDate('start_date', '>=', $today)
            ->whereDate('start_date', '<=', $today->copy()->addDays(7))
            ->orderBy('start_date')
            ->limit(10)
            ->get();

        // Lịch khởi hành đang chạy
        $runningDepartures = tour_departures::with(['tour', 'bookings', 'assignment.guide'])
            ->where('status', 'running')
            ->orderBy('start_date', 'desc')
            ->get();

        // Top tour theo booking
        $topTours = Tours::with('departures.bookings')
            ->withCount('departures')
            ->get()
            ->map(function ($tour) {
                return [
                    'tour' => $tour,
                    'booking_count' => $tour->departures->flatMap->bookings->count(),
                    'capacity_total' => $tour->departures->sum('capacity_total'),
                    'capacity_booked' => $tour->departures->sum('capacity_booked'),
                ];
            })
            ->sortByDesc('booking_count')
            ->take(10)
            ->values();

        // Hướng dẫn viên và công việc của họ
        $guides = User::where('role', 'tour_guide')
            ->with(['guideAssignments' => function ($q) use ($today) {
                $q->whereHas('departure', function ($subQ) use ($today) {
                    $subQ->whereDate('start_date', '>=', $today)
                        ->whereDate('start_date', '<=', $today->copy()->addDays(30));
                });
            }])
            ->get()
            ->sortByDesc(function ($guide) {
                return $guide->guideAssignments->count();
            })
            ->take(5);

        // Dịch vụ đối tác chờ xử lý
        $pendingServices = departure_services::where('status', 'pending')
            ->count();

        // Dữ liệu doanh thu theo ngày (7 ngày gần nhất)
        $last7Days = [];
        $revenueChart = [
            'labels' => [],
            'data' => []
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $revenue = orders::whereDate('created_at', $date)
                ->whereIn('status', ['paid', 'completed'])
                ->sum('total_amount');
            
            $revenueChart['labels'][] = $date->format('d/m');
            $revenueChart['data'][] = round($revenue, 0);
        }

        // Lịch khởi hành cần chốt đoàn
        $needConfirmList = tour_departures::with('tour')
            ->where('status', 'pending')
            ->whereDate('start_date', '<=', $today->copy()->addDays(5))
            ->orderBy('start_date')
            ->limit(10)
            ->get();

        // Tỷ lệ đặt tour
        $totalCapacity = tour_departures::sum('capacity_total');
        $totalBooked = tour_departures::sum('capacity_booked');
        $bookingRate = $totalCapacity > 0 
            ? round($totalBooked / $totalCapacity * 100, 1) 
            : 0;

        return view('admin.tour_manager.dashboard', compact(
            'user',
            'totalTours',
            'totalDepartures',
            'upcomingDeparturesCount',
            'runningDeparturesCount',
            'needConfirmDepartures',
            'monthlyRevenue',
            'dailyRevenue',
            'monthlyBookings',
            'upcomingDepartures',
            'runningDepartures',
            'topTours',
            'guides',
            'pendingServices',
            'revenueChart',
            'needConfirmList',
            'bookingRate'
        ));
    }
}
