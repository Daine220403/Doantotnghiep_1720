<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tours;
use App\Models\tour_departures;
use App\Models\bookings;
use App\Models\booking_passengers;
use App\Models\orders;
use App\Models\order_details;

class StaffBookingController extends Controller
{
    // Danh sách tour để nhân viên chọn xem/đặt tour cho khách
    public function index()
    {
        $tours = Tours::withCount('bookings')
            ->with('departures')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.staff_booking.tours_index', compact('tours'));
    }

    // Chi tiết 1 tour: thông tin tour + các lịch khởi hành + booking của khách
    public function showTour($id)
    {
        $tour = Tours::with([
            'departures.bookings.order',
            'departures.bookings.passengers',
        ])->findOrFail($id);

        return view('admin.staff_booking.tours_show', compact('tour'));
    }

    // Form đặt tour cho khách theo 1 lịch khởi hành cụ thể
    public function create($departureId)
    {
        $departure = tour_departures::with('tour')->findOrFail($departureId);
        $tour = $departure->tour;

        return view('admin.staff_booking.create', compact('departure', 'tour'));
    }

    // Xử lý tạo booking offline cho khách (không qua VNPay)
    public function store(Request $request)
    {
        // dd($request->all());
        $data = $request->validate([
            'tour_id' => 'required|exists:tours,id',
            'departure_id' => 'required|exists:tour_departures,id',
            'contact_name' => 'required|string|max:150',
            'contact_phone' => 'required|string|max:50',
            'contact_email' => 'nullable|email',
            'note' => 'nullable|string',
            'passengers' => 'required|array|min:1',
            'passengers.*.full_name' => 'required|string|max:150',
            'passengers.*.passenger_type' => 'required|in:adult,child,infant,youth',
            'passengers.*.gender' => 'nullable|in:male,female,other',
            'passengers.*.dob' => 'nullable|date',
            'passengers.*.id_no' => 'nullable|string|max:50',
            'passengers.*.single_room' => 'nullable|boolean',
        ], [], [
            'contact_name' => 'tên khách liên hệ',
            'contact_phone' => 'số điện thoại',
            'passengers' => 'danh sách hành khách',
            'passengers.*.full_name' => 'tên hành khách',
        ]);

        $tour = Tours::findOrFail($data['tour_id']);
        $departure = tour_departures::where('id', $data['departure_id'])
            ->where('tour_id', $tour->id)
            ->firstOrFail();
        
        // Tính số lượng khách theo loại
        $passengerCollection = collect($data['passengers']);
        $adults = $passengerCollection->where('passenger_type', 'adult')->count();
        $children = $passengerCollection->where('passenger_type', 'child')->count();
        $infants = $passengerCollection->where('passenger_type', 'infant')->count();
        $youths = $passengerCollection->where('passenger_type', 'youth')->count();
        $totalGuests = $adults + $children + $infants + $youths;

        // Kiểm tra số chỗ còn lại
        $remaining = $departure->capacity_total - $departure->capacity_booked;
        if ($totalGuests > $remaining) {
            return back()->withErrors([
                'departure_id' => 'Số lượng khách vượt quá số chỗ còn lại (' . max($remaining, 0) . ' chỗ)',
            ])->withInput();
        }

        // Đơn giá theo lịch khởi hành
        $priceAdult = (float) $departure->price_adult;
        $priceChild = (float) $departure->price_child;
        $priceInfant = (float) $departure->price_infant;
        $priceYouth = (float) $departure->price_youth;

        // Phụ thu phòng đơn
        $singleRoomCount = 0;
        foreach ($data['passengers'] as $p) {
            if (($p['passenger_type'] ?? null) === 'adult' && !empty($p['single_room'])) {
                $singleRoomCount++;
            }
        }
        $singleRoomPrice = (float) $departure->single_room_surcharge;
        $singleSurcharge = $singleRoomCount * $singleRoomPrice;

        $subtotal = $adults * $priceAdult
            + $children * $priceChild
            + $infants * $priceInfant
            + $youths * $priceYouth
            + $singleSurcharge;
        $discountTotal = 0;
        $totalAmount = $subtotal - $discountTotal;

        // Tạo đơn hàng offline cho khách (chưa thanh toán)
        $order = orders::create([
            'order_code' => 'OD' . now()->format('ymdHis'),
            'user_id' => null, // booking do nhân viên tạo hộ khách
            'contact_name' => $data['contact_name'],
            'contact_phone' => $data['contact_phone'],
            'contact_email' => $data['contact_email'] ?? null,
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'total_amount' => $totalAmount,
            'status' => 'pending',
        ]);

        // Tạo booking
        $booking = bookings::create([
            'order_id' => $order->id,
            'departure_id' => $departure->id,
            'note' => $data['note'] ?? null,
            'status' => 'pending',
        ]);

        // Lưu danh sách hành khách
        foreach ($data['passengers'] as $p) {
            $isSingle = !empty($p['single_room']) && ($p['passenger_type'] ?? 'adult') === 'adult';

            booking_passengers::create([
                'booking_id' => $booking->id,
                'full_name' => $p['full_name'],
                'gender' => $p['gender'] ?? null,
                'dob' => $p['dob'] ?? null,
                'id_no' => $p['id_no'] ?? null,
                'passenger_type' => $p['passenger_type'] ?? 'adult',
                'single_room' => $isSingle,
                'single_room_surcharge' => $isSingle ? $singleRoomPrice : 0,
            ]);
        }

        // Chi tiết đơn hàng theo loại khách
        if ($adults > 0) {
            order_details::create([
                'order_id' => $order->id,
                'item_type' => 'tour',
                'item_id' => $tour->id,
                'item_name' => $tour->title . ' - Người lớn',
                'qty' => $adults,
                'unit_price' => $priceAdult,
                'line_total' => $adults * $priceAdult,
                'meta' => json_encode([
                    'schedule_id' => $departure->id,
                    'schedule_date' => $departure->start_date,
                    'type' => 'adult',
                ]),
            ]);
        }

        if ($children > 0) {
            order_details::create([
                'order_id' => $order->id,
                'item_type' => 'tour',
                'item_id' => $tour->id,
                'item_name' => $tour->title . ' - Trẻ em',
                'qty' => $children,
                'unit_price' => $priceChild,
                'line_total' => $children * $priceChild,
                'meta' => json_encode([
                    'schedule_id' => $departure->id,
                    'schedule_date' => $departure->start_date,
                    'type' => 'child',
                ]),
            ]);
        }

        if ($infants > 0) {
            order_details::create([
                'order_id' => $order->id,
                'item_type' => 'tour',
                'item_id' => $tour->id,
                'item_name' => $tour->title . ' - Trẻ nhỏ',
                'qty' => $infants,
                'unit_price' => $priceInfant,
                'line_total' => $infants * $priceInfant,
                'meta' => json_encode([
                    'schedule_id' => $departure->id,
                    'schedule_date' => $departure->start_date,
                    'type' => 'infant',
                ]),
            ]);
        }

        if ($youths > 0) {
            order_details::create([
                'order_id' => $order->id,
                'item_type' => 'tour',
                'item_id' => $tour->id,
                'item_name' => $tour->title . ' - Em bé',
                'qty' => $youths,
                'unit_price' => $priceYouth,
                'line_total' => $youths * $priceYouth,
                'meta' => json_encode([
                    'schedule_id' => $departure->id,
                    'schedule_date' => $departure->start_date,
                    'type' => 'youth',
                ]),
            ]);
        }

        if ($singleSurcharge > 0) {
            order_details::create([
                'order_id' => $order->id,
                'item_type' => 'surcharge',
                'item_id' => $tour->id,
                'item_name' => $tour->title . ' - Phụ thu phòng đơn',
                'qty' => $singleRoomCount,
                'unit_price' => $singleRoomPrice,
                'line_total' => $singleSurcharge,
                'meta' => json_encode([
                    'schedule_id' => $departure->id,
                    'schedule_date' => $departure->start_date,
                    'type' => 'single_room_surcharge',
                    'single_room_count' => $singleRoomCount,
                ]),
            ]);
        }

        // Cập nhật số chỗ đã book của lịch khởi hành
        $departure->increment('capacity_booked', $totalGuests);

        return redirect()
            ->route('admin.staff-booking.tours.show', $tour->id)
            ->with('success', 'Đặt tour cho khách thành công.');
    }

    // Nhân viên hủy booking giúp khách
    public function cancel($id)
    {
        $booking = bookings::with(['departure', 'passengers', 'order'])->findOrFail($id);

        if ($booking->status === 'cancelled') {
            return back()->with('info', 'Booking này đã bị hủy trước đó.');
        }

        $passengerCount = $booking->passengers()->count();

        if ($booking->departure && $passengerCount > 0) {
            $booking->departure->decrement('capacity_booked', $passengerCount);
        }

        $booking->status = 'cancelled';
        $booking->save();

        if ($booking->order) {
            $booking->order->status = 'cancelled';
            $booking->order->save();
        }

        return back()->with('success', 'Đã hủy booking cho khách thành công.');
    }
}
