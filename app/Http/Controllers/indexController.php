<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tours;
use App\Models\Bookings;
use App\Models\Reviews;
use Illuminate\Support\Facades\Auth;

class indexController extends Controller
{
    public function index()
    {
        $tours = Tours::with([
            'images',
            'itineraries',
            'departures',
            'policies'
        ])
            ->where('status', 'published')
            ->latest()
            ->take(4)
            ->get();

        return view('index', compact('tours'));
    }

    public function show($slug)
    {
        $tour = Tours::with([
            'images',
            'itineraries',
            'departures',
            'policies',
            'reviews.user'
        ])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Reviews đã duyệt
        $reviews = $tour->reviews->where('status', 'approved')->values();

        $canReview = false;
        $hasReviewed = false;
        $reviewBooking = null;

        // Nếu user đăng nhập
        if (Auth::check()) {

            $userId = Auth::id();
            
            $reviewBooking = Bookings::where('status', 'completed')
                ->whereRelation('order', 'user_id', $userId)
                ->whereRelation('departure', 'tour_id', $tour->id)
                ->first();

            // Nếu có booking hợp lệ
            if ($reviewBooking) {

                $canReview = true;

                // Kiểm tra đã review chưa
                $hasReviewed = Reviews::where('booking_id', $reviewBooking->id)->exists();
            }
        }

        return view('tours.tour_show', compact(
            'tour',
            'reviews',
            'canReview',
            'hasReviewed',
            'reviewBooking'
        ));
    }
}
