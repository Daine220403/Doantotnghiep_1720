<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\orders;
use App\Models\bookings;
use App\Models\payments;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\booking_passengers;
use App\Models\order_details;
use App\Services\RefundService;

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
            ->whereIn('status', ['partial_paid', 'paid'])
            ->count();
        $totalSpent = orders::where('user_id', $user->id)
            ->whereIn('status', ['partial_paid', 'paid'])
            ->sum('total_amount');

        $upcomingBookings = bookings::with(['departure.tour', 'order'])
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->whereIn('status', ['pending', 'partial_paid', 'paid']);
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

        $recentBookings = bookings::with(['departure.tour', 'order'])
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->latest()
            ->limit(10)
            ->get();

        // Tính tổng tiền đã thanh toán cho từng đơn để hiển thị nhanh trên dashboard
        $orderIds = $upcomingBookings->pluck('order.id')
            ->merge($recentBookings->pluck('order.id'))
            ->filter()
            ->unique()
            ->values();

        $paidByOrder = [];

        if ($orderIds->isNotEmpty()) {
            $payments = payments::whereIn('order_id', $orderIds)
                ->where('status', 'success')
                ->get()
                ->groupBy('order_id');

            foreach ($payments as $orderId => $group) {
                $paidByOrder[$orderId] = $group->sum('amount');
            }
        }

        return view('dashboard', compact(
            'user',
            'totalOrders',
            'totalPaidOrders',
            'totalSpent',
            'upcomingBookings',
            'upcomingCount',
            'recentBookings',
            'paidByOrder'
        ));
    }

    public function showBooking($bookingId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('signin');
        }

        $booking = bookings::with(['order', 'departure.tour', 'passengers'])
            ->where('id', $bookingId)
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->firstOrFail();

        $order = $booking->order;

        $paidAmount = 0;
        $remainingAmount = 0;
        $isFullyPaid = false;
        $isDeposit = false;
        $lastPaymentType = null;

        if ($order) {
            $paymentsQuery = payments::where('order_id', $order->id)
                ->where('status', 'success');

            $paidAmount = (clone $paymentsQuery)->sum('amount');

            $latestPayment = (clone $paymentsQuery)
                ->orderByDesc('paid_at')
                ->orderByDesc('id')
                ->first();

            if ($latestPayment) {
                $lastPaymentType = $latestPayment->payment_type;
            }

            $remainingAmount = max(($order->total_amount ?? 0) - $paidAmount, 0);
            $isFullyPaid = $paidAmount >= (($order->total_amount ?? 0) - 1);
            $isDeposit = ($lastPaymentType === 'deposit') || ($paidAmount > 0 && !$isFullyPaid);
        }

        return view('bookings.show', [
            'user' => $user,
            'booking' => $booking,
            'paidAmount' => $paidAmount,
            'remainingAmount' => $remainingAmount,
            'isFullyPaid' => $isFullyPaid,
            'isDeposit' => $isDeposit,
            'lastPaymentType' => $lastPaymentType,
        ]);
    }

    public function cancelBooking($bookingId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('signin');
        }

        $booking = bookings::with(['order', 'departure', 'passengers'])
            ->where('id', $bookingId)
            ->firstOrFail();

        $order = $booking->order;

        if (!$order || $order->user_id !== $user->id) {
            return redirect()->route('dashboard')->with('error', 'Bạn không có quyền với đơn này');
        }

        if ($booking->status === 'cancelled') {
            return redirect()->route('dashboard')
                ->with('error', 'Đơn này đã được hủy trước đó');
        }

        $departure = $booking->departure;
        if ($departure && $departure->start_date) {
            $daysBeforeDeparture = Carbon::now()->diffInDays(Carbon::parse($departure->start_date), false);

            if ($daysBeforeDeparture < 7) {
                return redirect()->route('dashboard')
                    ->with('error', 'Bạn chỉ có thể hủy đơn trước 7 ngày khởi hành');
            }
        }

        try {
            $refundService = new RefundService();
            $refundRequest = $refundService->createRefundRequest($bookingId, $user->id);

            return redirect()->route('dashboard')->with('success', 
                'Yêu cầu hủy tour đã được gửi. Chúng tôi sẽ xử lý hoàn tiền trong vòng 24-48 giờ. Mã yêu cầu: ' . $refundRequest->refund_code
            );
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        }
    }

    public function editBooking($bookingId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('signin');
        }

        $booking = bookings::with(['order', 'departure.tour', 'passengers'])
            ->where('id', $bookingId)
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->firstOrFail();

        $order = $booking->order;
        $departure = $booking->departure;

        if (!$departure || !$departure->start_date) {
            return redirect()->route('dashboard')
                ->with('error', 'Không thể sửa đơn này vì thiếu thông tin lịch khởi hành');
        }

        $daysBeforeDeparture = Carbon::now()->diffInDays(Carbon::parse($departure->start_date), false);

        $paidAmount = 0;
        $isFullyPaid = false;

        if ($order) {
            $paymentsQuery = payments::where('order_id', $order->id)
                ->where('status', 'success');

            $paidAmount = (clone $paymentsQuery)->sum('amount');
            $isFullyPaid = $paidAmount >= (($order->total_amount ?? 0) - 1);
        }

        if ($daysBeforeDeparture < 7) {
            return redirect()->route('dashboard')
                ->with('error', 'Bạn chỉ có thể sửa thông tin trước 7 ngày khởi hành');
        }

        if ($isFullyPaid) {
            return redirect()->route('dashboard')
                ->with('error', 'Đơn đã thanh toán đủ, không thể sửa thông tin');
        }

        return view('bookings.edit', [
            'user' => $user,
            'booking' => $booking,
            'order' => $order,
            'paidAmount' => $paidAmount,
        ]);
    }

    public function updateBooking(Request $request, $bookingId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('signin');
        }

        $booking = bookings::with(['order', 'departure', 'passengers'])
            ->where('id', $bookingId)
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->firstOrFail();

        $order = $booking->order;
        $departure = $booking->departure;

        if (!$order) {
            return redirect()->route('dashboard')
                ->with('error', 'Không tìm thấy đơn hàng liên quan');
        }

        if (!$departure || !$departure->start_date) {
            return redirect()->route('dashboard')
                ->with('error', 'Không thể sửa đơn này vì thiếu thông tin lịch khởi hành');
        }

        $daysBeforeDeparture = Carbon::now()->diffInDays(Carbon::parse($departure->start_date), false);

        $paymentsQuery = payments::where('order_id', $order->id)
            ->where('status', 'success');

        $paidAmount = (clone $paymentsQuery)->sum('amount');
        $isFullyPaid = $paidAmount >= (($order->total_amount ?? 0) - 1);

        if ($daysBeforeDeparture < 7) {
            return redirect()->route('dashboard')
                ->with('error', 'Bạn chỉ có thể sửa thông tin trước 7 ngày khởi hành');
        }

        if ($isFullyPaid) {
            return redirect()->route('dashboard')
                ->with('error', 'Đơn đã thanh toán đủ, không thể sửa thông tin');
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

            // cập nhật ghi chú booking (không lưu trạng thái/phụ thu phòng đơn ở cấp booking nữa)
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
                    // dùng đơn giá phụ thu đang áp dụng cho booking (đã được chốt ở trên)
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

        return redirect()->route('dashboard.bookings.show', $booking->id)
            ->with('success', 'Cập nhật thông tin đơn đặt tour thành công. Giá đã được tính lại dựa trên danh sách hành khách mới.');
    }
}
