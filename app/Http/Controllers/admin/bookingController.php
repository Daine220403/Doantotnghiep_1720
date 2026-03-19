<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\bookings;

class bookingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        // departure.tour là để lấy thông tin tour liên quan đến lịch 
        // khởi hành của booking, giúp hiển thị tên tour trong danh sách booking
        $query = bookings::with(['order', 'departure.tour'])->latest();  

        if ($status) {
            $query->where('status', $status);
        }

        $bookings = $query->get();

        return view('admin.mana_booking.index', compact('bookings', 'status'));
    }

    public function show($id)
    {
        $booking = bookings::with(['order', 'departure.tour','passengers'])->findOrFail($id);
        return view('admin.mana_booking.show', compact('booking'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,paid,cancelled,completed',
        ]);

        $booking = bookings::findOrFail($id);
        $booking->status = $request->input('status');
        $booking->save();

        return redirect()->back()->with('success', 'Cập nhật trạng thái booking thành công.');
    }
}
