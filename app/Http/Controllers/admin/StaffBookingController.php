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
use App\Models\payments;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StaffBookingController extends Controller
{
    // Danh sách tour để nhân viên chọn xem/đặt tour cho khách
    public function index(Request $request)
    {
        $tourType = $request->input('tour_type');
        $destination = trim((string) $request->input('destination', ''));
        $durationDays = $request->input('duration_days');
        $priceMin = $request->input('price_min');
        $priceMax = $request->input('price_max');

        $toursQuery = Tours::withCount('bookings')
            ->with('departures')
            ->orderBy('created_at', 'desc');

        if (in_array($tourType, ['domestic', 'international'], true)) {
            $toursQuery->where('tour_type', $tourType);
        }

        if ($destination !== '') {
            $toursQuery->where('destination_text', 'like', '%' . $destination . '%');
        }

        if ($durationDays !== null && $durationDays !== '' && is_numeric($durationDays)) {
            $toursQuery->where('duration_days', (int) $durationDays);
        }

        if ($priceMin !== null && $priceMin !== '') {
            $normalizedMin = (float) str_replace(',', '', (string) $priceMin);
            $toursQuery->where('base_price_from', '>=', $normalizedMin);
        }

        if ($priceMax !== null && $priceMax !== '') {
            $normalizedMax = (float) str_replace(',', '', (string) $priceMax);
            $toursQuery->where('base_price_from', '<=', $normalizedMax);
        }

        $tours = $toursQuery->get();

        $destinations = Tours::query()
            ->whereNotNull('destination_text')
            ->where('destination_text', '<>', '')
            ->distinct()
            ->orderBy('destination_text')
            ->pluck('destination_text');

        return view('admin.staff_booking.tours_index', compact('tours', 'destinations'));
    }

    // Chi tiết 1 tour: thông tin tour + các lịch khởi hành + booking của khách
    public function showTour($id)
    {
        $tour = Tours::with([
            'departures.bookings.order',
            'departures.bookings.passengers',
        ])->findOrFail($id);

        // Tính tiền đã thanh toán và còn lại cho từng booking dựa trên orders + payments
        $orderIds = [];
        foreach ($tour->departures as $departure) {
            foreach ($departure->bookings as $booking) {
                if ($booking->order) {
                    $orderIds[] = $booking->order->id;
                }
            }
        }

        $paymentsByOrder = [];
        if (!empty($orderIds)) {
            $payments = payments::whereIn('order_id', $orderIds)
                ->where('status', 'success')
                ->get();

            foreach ($payments as $payment) {
                $paymentsByOrder[$payment->order_id] = ($paymentsByOrder[$payment->order_id] ?? 0) + $payment->amount;
            }
        }

        foreach ($tour->departures as $departure) {
            foreach ($departure->bookings as $booking) {
                $order = $booking->order;
                if ($order) {
                    $total = (float) ($order->total_amount ?? 0);
                    $paid = (float) ($paymentsByOrder[$order->id] ?? 0);
                    $remaining = max($total - $paid, 0);

                    // gắn thêm thuộc tính tạm cho view
                    $booking->total_amount = $total;
                    $booking->paid_amount = $paid;
                    $booking->remaining_amount = $remaining;
                }
            }
        }

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

    // Form xem / cập nhật lại thông tin booking + hành khách (dành cho nhân viên)
    public function edit($bookingId)
    {
        $booking = bookings::with(['order', 'departure.tour', 'passengers'])
            ->where('id', $bookingId)
            ->firstOrFail();

        $order = $booking->order;
        $departure = $booking->departure;

        if (!$order || !$departure) {
            return redirect()->back()->with('error', 'Không đủ thông tin đơn hàng hoặc lịch khởi hành để chỉnh sửa.');
        }

        $paidAmount = 0;
        $isFullyPaid = false;

        $paymentsQuery = payments::where('order_id', $order->id)
            ->where('status', 'success');

        $paidAmount = (clone $paymentsQuery)->sum('amount');
        $totalAmount = $order->total_amount ?? 0;
        $isFullyPaid = $paidAmount >= ($totalAmount - 1); // cho phép lệch nhỏ do làm tròn

        if ($isFullyPaid || $order->status === 'paid') {
            return redirect()->back()->with('error', 'Đơn đã thanh toán đủ, không thể sửa thông tin.');
        }

        if ($booking->status === 'cancelled') {
            return redirect()->back()->with('error', 'Booking đã bị huỷ, không thể sửa thông tin.');
        }

        return view('admin.staff_booking.edit', [
            'booking' => $booking,
            'order' => $order,
            'paidAmount' => $paidAmount,
        ]);
    }

    // Cập nhật lại thông tin booking + hành khách (nhân viên thao tác offline)
    public function update(Request $request, $bookingId)
    {
        $booking = bookings::with(['order', 'departure', 'passengers'])
            ->where('id', $bookingId)
            ->firstOrFail();

        $order = $booking->order;
        $departure = $booking->departure;

        if (!$order) {
            return redirect()->back()
                ->with('error', 'Không tìm thấy đơn hàng liên quan đến booking này.');
        }

        if (!$departure || !$departure->start_date) {
            return redirect()->back()
                ->with('error', 'Không thể sửa đơn này vì thiếu thông tin lịch khởi hành.');
        }

        // Không cho sửa nếu đơn đã thanh toán đủ
        $paymentsQuery = payments::where('order_id', $order->id)
            ->where('status', 'success');

        $paidAmount = (clone $paymentsQuery)->sum('amount');
        $totalAmount = $order->total_amount ?? 0;
        $isFullyPaid = $paidAmount >= ($totalAmount - 1);

        if ($isFullyPaid || $order->status === 'paid') {
            return redirect()->back()->with('error', 'Đơn đã thanh toán đủ, không thể sửa thông tin.');
        }

        if ($booking->status === 'cancelled') {
            return redirect()->back()->with('error', 'Booking đã bị huỷ, không thể sửa thông tin.');
        }

        $validated = $request->validate([
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'contact_email' => 'required|email|max:255',
            'note' => 'nullable|string|max:1000',
            'passengers' => 'required|array|min:1',
            'passengers.*.id' => 'nullable|integer',
            'passengers.*.full_name' => 'required|string|max:255',
            'passengers.*.gender' => 'nullable|in:male,female,other',
            'passengers.*.dob' => 'nullable|date',
            'passengers.*.passenger_type' => 'required|in:adult,child,infant,youth',
            'passengers.*.single_room' => 'nullable|boolean',
        ]);

        $passengersData = $validated['passengers'];

        // Tính lại tổng số khách mới và kiểm tra số chỗ còn lại
        $newGuestCount = count($passengersData);
        $oldGuestCount = $booking->passengers->count();

        $capacityTotal = (int) $departure->capacity_total;
        $capacityBooked = (int) $departure->capacity_booked;
        $availableSlots = $capacityTotal - $capacityBooked + $oldGuestCount; // hoàn lại số chỗ cũ của booking này

        if ($newGuestCount > $availableSlots) {
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'passengers' => 'Số lượng khách vượt quá số chỗ còn lại (' . max($availableSlots, 0) . ' chỗ)',
                ]);
        }

        // Ràng buộc ngày sinh theo loại khách (tính theo ngày khởi hành)
        $ageErrors = [];
        $departureDate = Carbon::parse($departure->start_date);

        foreach ($passengersData as $idx => $p) {
            $type = $p['passenger_type'] ?? null;
            $dob = $p['dob'] ?? null;

            if (empty($dob)) {
                $ageErrors["passengers.$idx.dob"] = 'Vui lòng nhập ngày sinh.';
                continue;
            }

            try {
                $dobDate = Carbon::parse($dob);
            } catch (\Exception $e) {
                $ageErrors["passengers.$idx.dob"] = 'Ngày sinh không hợp lệ.';
                continue;
            }

            $ageYears = $dobDate->diffInYears($departureDate);

            switch ($type) {
                case 'adult':
                    if ($ageYears < 12) {
                        $ageErrors["passengers.$idx.dob"] = 'Người lớn phải từ 12 tuổi trở lên tính đến ngày khởi hành.';
                    }
                    break;
                case 'child':
                    if ($ageYears < 5 || $ageYears > 11) {
                        $ageErrors["passengers.$idx.dob"] = 'Trẻ em phải từ 5 đến 11 tuổi tính đến ngày khởi hành.';
                    }
                    break;
                case 'infant':
                    if ($ageYears < 2 || $ageYears > 4) {
                        $ageErrors["passengers.$idx.dob"] = 'Trẻ nhỏ phải từ 2 đến 4 tuổi tính đến ngày khởi hành.';
                    }
                    break;
                case 'youth':
                    if ($ageYears >= 2) {
                        $ageErrors["passengers.$idx.dob"] = 'Em bé phải dưới 2 tuổi tính đến ngày khởi hành.';
                    }
                    break;
                default:
                    break;
            }
        }

        if (!empty($ageErrors)) {
            return redirect()->back()->withInput()->withErrors($ageErrors);
        }

        // Tính giá mới dựa trên danh sách hành khách
        $adultCount = 0;
        $childCount = 0;
        $infantCount = 0;
        $youthCount = 0;
        $singleRoomCount = 0;

        foreach ($passengersData as $p) {
            $type = $p['passenger_type'] ?? 'adult';
            switch ($type) {
                case 'child':
                    $childCount++;
                    break;
                case 'infant':
                    $infantCount++;
                    break;
                case 'youth':
                    $youthCount++;
                    break;
                default:
                    $adultCount++;
                    if (!empty($p['single_room'])) {
                        $singleRoomCount++;
                    }
                    break;
            }
        }

        $priceAdult = (float) $departure->price_adult;
        $priceChild = (float) $departure->price_child;
        $priceInfant = (float) $departure->price_infant;
        $priceYouth = (float) $departure->price_youth;
        $singleRoomSurcharge = (float) $departure->single_room_surcharge;

        $singleSurcharge = $singleRoomCount * $singleRoomSurcharge;

        $subtotal = $adultCount * $priceAdult
            + $childCount * $priceChild
            + $infantCount * $priceInfant
            + $youthCount * $priceYouth
            + $singleSurcharge;

        $discountTotal = 0; // hiện tại chưa áp dụng khuyến mãi khi sửa
        $totalAmount = $subtotal - $discountTotal;

        DB::transaction(function () use (
            $booking,
            $order,
            $departure,
            $validated,
            $passengersData,
            $oldGuestCount,
            $newGuestCount,
            $subtotal,
            $discountTotal,
            $totalAmount,
            $adultCount,
            $childCount,
            $infantCount,
            $youthCount,
            $singleRoomCount,
            $priceAdult,
            $priceChild,
            $priceInfant,
            $priceYouth,
            $singleRoomSurcharge
        ) {
            // Cập nhật thông tin liên hệ + ghi chú
            $order->contact_name = $validated['contact_name'];
            $order->contact_phone = $validated['contact_phone'];
            $order->contact_email = $validated['contact_email'];
            $order->subtotal = $subtotal;
            $order->discount_total = $discountTotal;
            $order->total_amount = $totalAmount;
            $order->save();

            // cập nhật ghi chú booking
            $booking->note = $validated['note'] ?? $booking->note;
            $booking->save();

            // Cập nhật lại số chỗ đã đặt cho lịch khởi hành
            $departure->capacity_booked = max(0, (int) $departure->capacity_booked - $oldGuestCount + $newGuestCount);
            $departure->save();

            // Xóa danh sách hành khách cũ và tạo lại theo input mới
            $booking->passengers()->delete();

            foreach ($passengersData as $p) {
                $isSingle = !empty($p['single_room']) && ($p['passenger_type'] ?? 'adult') === 'adult';

                booking_passengers::create([
                    'booking_id' => $booking->id,
                    'full_name' => $p['full_name'],
                    'gender' => $p['gender'] ?? null,
                    'dob' => $p['dob'] ?? null,
                    'id_no' => $p['id_no'] ?? null,
                    'passenger_type' => $p['passenger_type'] ?? 'adult',
                    'single_room' => $isSingle,
                    'single_room_surcharge' => $isSingle ? $singleRoomSurcharge : 0,
                ]);
            }

            // Cập nhật lại chi tiết đơn hàng theo cơ cấu khách mới
            order_details::where('order_id', $order->id)->delete();

            $tour = optional($departure->tour);

            if ($tour) {
                if ($adultCount > 0) {
                    order_details::create([
                        'order_id' => $order->id,
                        'item_type' => 'tour',
                        'item_id' => $tour->id,
                        'item_name' => $tour->title . ' - Người lớn',
                        'qty' => $adultCount,
                        'unit_price' => $priceAdult,
                        'line_total' => $adultCount * $priceAdult,
                        'meta' => json_encode([
                            'schedule_id' => $departure->id,
                            'schedule_date' => $departure->start_date,
                            'type' => 'adult',
                        ]),
                    ]);
                }

                if ($childCount > 0) {
                    order_details::create([
                        'order_id' => $order->id,
                        'item_type' => 'tour',
                        'item_id' => $tour->id,
                        'item_name' => $tour->title . ' - Trẻ em',
                        'qty' => $childCount,
                        'unit_price' => $priceChild,
                        'line_total' => $childCount * $priceChild,
                        'meta' => json_encode([
                            'schedule_id' => $departure->id,
                            'schedule_date' => $departure->start_date,
                            'type' => 'child',
                        ]),
                    ]);
                }

                if ($infantCount > 0) {
                    order_details::create([
                        'order_id' => $order->id,
                        'item_type' => 'tour',
                        'item_id' => $tour->id,
                        'item_name' => $tour->title . ' - Trẻ nhỏ',
                        'qty' => $infantCount,
                        'unit_price' => $priceInfant,
                        'line_total' => $infantCount * $priceInfant,
                        'meta' => json_encode([
                            'schedule_id' => $departure->id,
                            'schedule_date' => $departure->start_date,
                            'type' => 'infant',
                        ]),
                    ]);
                }

                if ($youthCount > 0) {
                    order_details::create([
                        'order_id' => $order->id,
                        'item_type' => 'tour',
                        'item_id' => $tour->id,
                        'item_name' => $tour->title . ' - Em bé',
                        'qty' => $youthCount,
                        'unit_price' => $priceYouth,
                        'line_total' => $youthCount * $priceYouth,
                        'meta' => json_encode([
                            'schedule_id' => $departure->id,
                            'schedule_date' => $departure->start_date,
                            'type' => 'youth',
                        ]),
                    ]);
                }

                if ($singleRoomCount > 0 && $singleRoomSurcharge > 0) {
                    $singleTotal = $singleRoomCount * $singleRoomSurcharge;

                    order_details::create([
                        'order_id' => $order->id,
                        'item_type' => 'surcharge',
                        'item_id' => $tour->id,
                        'item_name' => $tour->title . ' - Phụ thu phòng đơn',
                        'qty' => $singleRoomCount,
                        'unit_price' => $singleRoomSurcharge,
                        'line_total' => $singleTotal,
                        'meta' => json_encode([
                            'schedule_id' => $departure->id,
                            'schedule_date' => $departure->start_date,
                            'type' => 'single_room_surcharge',
                            'single_room_count' => $singleRoomCount,
                        ]),
                    ]);
                }
            }
        });

        return redirect()->route('admin.staff-booking.tours.show', $departure->tour_id)
            ->with('success', 'Cập nhật thông tin booking và hành khách thành công. Giá đã được tính lại dựa trên danh sách hành khách mới.');
    }

    // Ghi nhận thanh toán cọc 30% (offline)
    public function deposit($bookingId)
    {
        $booking = bookings::with(['order'])->findOrFail($bookingId);
        $order = $booking->order;

        if (!$order) {
            return back()->with('error', 'Không tìm thấy đơn hàng liên quan để ghi nhận thanh toán.');
        }

        if ($booking->status === 'cancelled' || $order->status === 'cancelled') {
            return back()->with('error', 'Booking / đơn hàng đã bị hủy, không thể ghi nhận thanh toán.');
        }

        $totalAmount = $order->total_amount ?? 0;
        if ($totalAmount <= 0) {
            return back()->with('error', 'Tổng tiền đơn hàng không hợp lệ.');
        }

        $paidAmount = payments::where('order_id', $order->id)
            ->where('status', 'success')
            ->sum('amount');

        $remainingAmount = max($totalAmount - $paidAmount, 0);

        if ($remainingAmount <= 0) {
            return back()->with('info', 'Đơn này đã được thanh toán đủ.');
        }

        $depositTarget = round($totalAmount * 0.3, 2);

        if ($paidAmount >= ($depositTarget - 1)) {
            return back()->with('info', 'Đơn đã được đặt cọc hoặc thanh toán vượt quá 30%.');
        }

        $amountToPay = min($depositTarget - $paidAmount, $remainingAmount);

        payments::create([
            'order_id' => $order->id,
            'payment_code' => 'PM' . now()->format('YmdHis') . rand(100, 999),
            'payment_type' => 'deposit',
            'method' => 'offline',
            'amount' => $amountToPay,
            'status' => 'success',
            'paid_at' => now(),
            'transaction_ref' => null,
            'raw_response' => null,
        ]);

        $this->syncOrderAndBookingStatus($order, $booking);

        return back()->with('success', 'Đã ghi nhận thanh toán cọc 30% cho booking.');
    }

    // Ghi nhận thanh toán đủ (offline)
    public function payFull($bookingId)
    {
        $booking = bookings::with(['order'])->findOrFail($bookingId);
        $order = $booking->order;

        if (!$order) {
            return back()->with('error', 'Không tìm thấy đơn hàng liên quan để ghi nhận thanh toán.');
        }

        if ($booking->status === 'cancelled' || $order->status === 'cancelled') {
            return back()->with('error', 'Booking / đơn hàng đã bị hủy, không thể ghi nhận thanh toán.');
        }

        $totalAmount = $order->total_amount ?? 0;
        if ($totalAmount <= 0) {
            return back()->with('error', 'Tổng tiền đơn hàng không hợp lệ.');
        }

        $paidAmount = payments::where('order_id', $order->id)
            ->where('status', 'success')
            ->sum('amount');

        $remainingAmount = max($totalAmount - $paidAmount, 0);

        if ($remainingAmount <= 0) {
            return back()->with('info', 'Đơn này đã được thanh toán đủ.');
        }

        payments::create([
            'order_id' => $order->id,
            'payment_code' => 'PM' . now()->format('YmdHis') . rand(100, 999),
            'payment_type' => 'full',
            'method' => 'offline',
            'amount' => $remainingAmount,
            'status' => 'success',
            'paid_at' => now(),
            'transaction_ref' => null,
            'raw_response' => null,
        ]);

        $this->syncOrderAndBookingStatus($order, $booking);

        return back()->with('success', 'Đã ghi nhận thanh toán đủ cho booking.');
    }

    // Đồng bộ trạng thái orders & bookings theo tổng tiền đã thanh toán
    protected function syncOrderAndBookingStatus(orders $order, bookings $booking): void
    {
        $totalAmount = $order->total_amount ?? 0;
        $totalPaid = payments::where('order_id', $order->id)
            ->where('status', 'success')
            ->sum('amount');

        if ($totalPaid >= ($totalAmount - 1)) {
            $order->status = 'paid';
            $order->save();

            if ($booking->status !== 'paid') {
                $booking->status = 'paid';
                $booking->save();
            }
        } elseif ($totalPaid > 0) {
            $order->status = 'partial_paid';
            $order->save();

            if (!in_array($booking->status, ['confirmed', 'paid'])) {
                $booking->status = 'confirmed';
                $booking->save();
            }
        } else {
            $order->status = 'pending';
            $order->save();

            if (!in_array($booking->status, ['pending', 'cancelled'])) {
                $booking->status = 'pending';
                $booking->save();
            }
        }
    }
}
