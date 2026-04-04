<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tours;
use App\Models\tour_departures;
use App\Models\bookings;
use App\Models\orders;
use App\Models\departure_services;
use App\Models\partners;
use App\Models\User;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class dashboardController extends Controller
{
	public function index(Request $request)
	{
		$today = Carbon::today();
		$startOfMonth = $today->copy()->startOfMonth();
		$endOfMonth = $today->copy()->endOfMonth();

		$totalTours = Tours::count();

		$upcomingDeparturesCount = tour_departures::whereDate('start_date', '>=', $today)
			->whereDate('start_date', '<=', $today->copy()->addDays(30))
			->count();

		$todayBookings = bookings::whereDate('created_at', $today)->count();

		$revenueRange = $request->get('revenue_range', 'this_month');
		$revenueFrom = $startOfMonth->copy();
		$revenueTo = $endOfMonth->copy();
		$revenueFilterLabel = 'tháng này';

		switch ($revenueRange) {
			case 'today':
				$revenueFrom = $today->copy()->startOfDay();
				$revenueTo = $today->copy()->endOfDay();
				$revenueFilterLabel = 'hôm nay';
				break;
			case 'last_7_days':
				$revenueFrom = $today->copy()->subDays(6)->startOfDay();
				$revenueTo = $today->copy()->endOfDay();
				$revenueFilterLabel = '7 ngày qua';
				break;
			case 'last_30_days':
				$revenueFrom = $today->copy()->subDays(29)->startOfDay();
				$revenueTo = $today->copy()->endOfDay();
				$revenueFilterLabel = '30 ngày qua';
				break;
			case 'last_month':
				$lastMonth = $today->copy()->subMonthNoOverflow();
				$revenueFrom = $lastMonth->copy()->startOfMonth();
				$revenueTo = $lastMonth->copy()->endOfMonth();
				$revenueFilterLabel = 'tháng trước';
				break;
			case 'custom':
				if ($request->filled('revenue_from')) {
					$revenueFrom = Carbon::parse($request->get('revenue_from'))->startOfDay();
				}
				if ($request->filled('revenue_to')) {
					$revenueTo = Carbon::parse($request->get('revenue_to'))->endOfDay();
				}
				if ($revenueFrom->gt($revenueTo)) {
					[$revenueFrom, $revenueTo] = [$revenueTo->copy()->startOfDay(), $revenueFrom->copy()->endOfDay()];
				}
				$revenueFilterLabel = 'khoảng tuỳ chọn';
				break;
			default:
				// giữ mặc định tháng này
				break;
		}

		$monthlyRevenue = orders::whereBetween('created_at', [$revenueFrom, $revenueTo])
			->whereIn('status', ['paid', 'completed'])
			->sum('total_amount');

		$revenueDaily = orders::selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
			->whereBetween('created_at', [$revenueFrom, $revenueTo])
			->whereIn('status', ['paid', 'completed'])
			->groupBy('date')
			->orderBy('date')
			->get();

		$revenueChart = [
			'labels' => $revenueDaily->pluck('date')->map(function ($date) {
				return Carbon::parse($date)->format('d/m');
			})->values(),
			'data' => $revenueDaily->pluck('total')->map(function ($value) {
				return round($value, 0);
			})->values(),
		];

		$last7 = $today->copy()->subDays(6);
		$last30 = $today->copy()->subDays(29);

		$bookings7 = bookings::whereBetween('created_at', [$last7, $today])->count();
		$bookings30 = bookings::whereBetween('created_at', [$last30, $today])->count();

		$totalWithStatus = bookings::whereNotNull('status')->count();
		$cancelCount = bookings::where('status', 'cancelled')->count();
		$cancelRate = $totalWithStatus > 0
			? round($cancelCount / $totalWithStatus * 100, 1) . '%'
			: '0%';

		$bookingStats = [
			'last_7_days' => $bookings7,
			'last_30_days' => $bookings30,
			'cancel_rate' => $cancelRate,
		];

		$runningToursCount = tour_departures::whereIn('status', ['confirmed', 'running', 'completed'])->count();

		$pendingPartnerRequests = departure_services::where('status', 'pending')->count();

		$needConfirmDepartures = tour_departures::where('status', 'pending')->count();

		$staffCount = User::whereIn('role', ['staff', 'staff_manager'])->count();
		$guideCount = User::where('role', 'tour_guide')->count();
		$partnerCount = partners::count();

		$pendingLeaves = LeaveRequest::where('status', 'pending')->count();

		$upcomingDepartures = tour_departures::with('tour')
			->whereDate('start_date', '>=', $today)
			->whereDate('start_date', '<=', $today->copy()->addDays(7))
			->orderBy('start_date')
			->limit(10)
			->get();

		$latestBookings = bookings::with(['order', 'departure.tour'])
			->orderByDesc('created_at')
			->limit(10)
			->get();

		return view('admin.index', compact(
			'totalTours',
			'upcomingDeparturesCount',
			'todayBookings',
			'monthlyRevenue',
			'revenueRange',
			'revenueFrom',
			'revenueTo',
			'revenueFilterLabel',
			'revenueChart',
			'bookingStats',
			'runningToursCount',
			'pendingPartnerRequests',
			'needConfirmDepartures',
			'staffCount',
			'guideCount',
			'partnerCount',
			'pendingLeaves',
			'upcomingDepartures',
			'latestBookings'
		));
	}
}
