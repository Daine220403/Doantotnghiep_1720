<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\reviews;
use Illuminate\Http\Request;

class ReviewsController extends Controller
{
    public function index(Request $request)
    {
        $query = reviews::with(['user', 'tour', 'booking.departure'])
            ->orderBy('created_at', 'desc');

        // Lọc theo trạng thái
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Lọc theo tour
        if ($tourId = $request->input('tour_id')) {
            $query->where('tour_id', $tourId);
        }

        // Lọc theo rating
        if ($rating = $request->input('rating')) {
            $query->where('rating', $rating);
        }

        $reviews = $query->paginate(20);

        // Thống kê
        $stats = [
            'total' => reviews::count(),
            'pending' => reviews::where('status', 'pending')->count(),
            'approved' => reviews::where('status', 'approved')->count(),
            'hidden' => reviews::where('status', 'hidden')->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    public function show(reviews $review)
    {
        $review->load(['user', 'tour', 'booking.departure', 'booking.order']);

        return view('admin.reviews.show', compact('review'));
    }

    public function approve(Request $request, reviews $review)
    {
        $review->update(['status' => 'approved']);

        return redirect()->back()->with('success', 'Đã duyệt đánh giá thành công.');
    }

    public function reject(Request $request, reviews $review)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $review->update([
            'status' => 'hidden',
            'content' => $review->content . "\n\n[Từ chối: " . ($validated['reason'] ?? 'Không có lý do') . "]"
        ]);

        return redirect()->back()->with('success', 'Đã từ chối đánh giá.');
    }
}