<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\tour_assignments;
use App\Models\Tours;
use Illuminate\Http\Request;

class guideController extends Controller
{
    public function index()
    {
        $guides = User::withCount(['guideAssignments as assignments_count'])
            ->where('role', 'tour_guide')
            ->orderBy('name')
            ->get();

        return view('admin.mana_guide.index', compact('guides'));
    }

    public function showTours(User $guide)
    {
        if ($guide->role !== 'tour_guide') {
            abort(404);
        }

        $tours = Tours::withCount('departures')
            ->latest()
            ->get();

        return view('admin.mana_guide.assign', compact('guide', 'tours'));
    }

    public function showDepartures(User $guide, Tours $tour)
    {
        if ($guide->role !== 'tour_guide') {
            abort(404);
        }

        $tour->load(['departures.assignment']);

        return view('admin.mana_guide.departures', compact('guide', 'tour'));
    }
}
