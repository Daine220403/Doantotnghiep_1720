<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\bookings;
use App\Models\Tours;

class bookingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $customerName = $request->query('customer_name');
        $customerPhone = $request->query('customer_phone');
        // departure.tour là để lấy thông tin tour liên quan đến lịch 
        // khởi hành của booking, giúp hiển thị tên tour trong danh sách booking
        $query = bookings::with(['order', 'departure.tour'])->latest();  

        if ($status) {
            $query->where('status', $status);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($customerName) {
            $query->whereHas('order', function ($q) use ($customerName) {
                $q->where('contact_name', 'like', '%' . $customerName . '%');
            });
        }

        if ($customerPhone) {
            $query->whereHas('order', function ($q) use ($customerPhone) {
                $q->where('contact_phone', 'like', '%' . $customerPhone . '%');
            });
        }

        $bookings = $query->get();

        return view('admin.mana_booking.index', compact(
            'bookings',
            'status',
            'startDate',
            'endDate',
            'customerName',
            'customerPhone'
        ));
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

    // Danh sách tour để xem khách đã booking
    public function tourCustomerIndex()
    {
        $tours = Tours::withCount('bookings')
            ->with('departures')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.mana_booking.tour_index', compact('tours'));
    }

    // Chi tiết 1 tour: thông tin tour + danh sách khách đã booking
    public function tourCustomerShow($id)
    {
        $tour = Tours::with([
            'departures.bookings.order',
            'departures.bookings.passengers',
        ])->findOrFail($id);

        return view('admin.mana_booking.tour_show', compact('tour'));
    }


}
