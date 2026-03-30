<?php

namespace App\Http\Controllers\guide;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\tour_departures;
use App\Models\bookings;
use App\Models\booking_passengers;
use Illuminate\Http\Request;

class TourGuideController extends Controller
{
    // Danh sách tour/lịch khởi hành được phân công cho hướng dẫn viên
    public function index()
    {
        $user = Auth::user();
        // dd($user);
        // chỉ cho phép người dùng có role 'tour_guide' và 'admin' và đang hoạt động truy cập
        if (!$user || ($user->role !== 'tour_guide' && $user->role !== 'admin') || $user->status !== 'active') {
            abort(403);
        }

        $departures = tour_departures::with(['tour', 'assignment'])
            ->whereHas('assignment', function ($q) use ($user) {
                $q->where('guide_id', $user->id);
            })
            ->whereIn('status', ['confirmed', 'running', 'completed'])
            ->orderBy('start_date', 'asc')
            ->get();

        return view('admin.tour_guide.assignments_index', compact('user', 'departures'));
    }

    // Chi tiết 1 lịch khởi hành + danh sách khách
    public function showDeparture($departureId)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'tour_guide') {
            abort(403);
        }

        $departure = tour_departures::with([
            'tour',
            'assignment.guide',
            'bookings.order',
            'bookings.passengers',
        ])->findOrFail($departureId);

        if (!$departure->assignment || $departure->assignment->guide_id !== $user->id) {
            abort(403);
        }

        return view('admin.tour_guide.departure_show', compact('user', 'departure'));
    }

    // Báo cáo thống kê cho 1 lịch khởi hành
    public function report($departureId)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'tour_guide') {
            abort(403);
        }

        $departure = tour_departures::with([
            'tour',
            'bookings.order',
            'bookings.passengers',
        ])->findOrFail($departureId);

        if (!$departure->assignment || $departure->assignment->guide_id !== $user->id) {
            abort(403);
        }

        $bookings = $departure->bookings;

        $totalBookings = $bookings->count();
        $totalPassengers = 0;
        $byType = [
            'adult' => 0,
            'child' => 0,
            'infant' => 0,
            'youth' => 0,
        ];
        $singleRoomCount = 0;
        $singleRoomSurchargeTotal = 0;

        foreach ($bookings as $booking) {
            foreach ($booking->passengers as $p) {
                $totalPassengers++;
                $type = $p->passenger_type ?? 'adult';
                if (isset($byType[$type])) {
                    $byType[$type]++;
                }

                if ($p->single_room) {
                    $singleRoomCount++;
                    $singleRoomSurchargeTotal += (float) ($p->single_room_surcharge ?? 0);
                }
            }
        }

        return view('admin.tour_guide.departure_report', [
            'user' => $user,
            'departure' => $departure,
            'totalBookings' => $totalBookings,
            'totalPassengers' => $totalPassengers,
            'byType' => $byType,
            'singleRoomCount' => $singleRoomCount,
            'singleRoomSurchargeTotal' => $singleRoomSurchargeTotal,
        ]);
    }
}
