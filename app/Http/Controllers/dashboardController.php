<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\orders;
use App\Models\bookings;

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

        return view('dashboard', compact(
            'user',
            'totalOrders',
            'totalPaidOrders',
            'totalSpent',
            'upcomingBookings',
            'upcomingCount'
        ));
    }
}
