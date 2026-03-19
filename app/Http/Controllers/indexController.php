<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tours;
use App\Models\Bookings;
use App\Models\Reviews;
use App\Models\orders;
use App\Models\order_details;
use App\Models\tour_departures;
use Illuminate\Support\Facades\Auth;

class indexController extends Controller
{
    public function index()
    {
        $tours = Tours::with([
            'images',
            'itineraries',
            'departures',
            'policies',
            'reviews'
        ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 'published')
            ->latest()
            ->take(4)
            ->get();

        return view('index', compact('tours'));
    }


    public function tours(Request $request)
    {
        // Khởi tạo query cơ bản cho danh sách tour,
        // load kèm các quan hệ cần dùng ở giao diện
        $query = Tours::with([
            'images' => function ($query) {
                $query->orderBy('sort_order');
            },
            'itineraries' => function ($query) {
                $query->orderBy('day_no');
            },
            'departures' => function ($query) {
                $query->orderBy('start_date');
            },
            'policies',
            'reviews.user' // Load user của review để hiển thị tên người đánh giá
        ])
            // Chỉ lấy các tour đang được publish
            ->where('status', 'published');

        // Nếu có từ khóa q gửi lên (ô tìm kiếm)

        if ($search = $request->input('q')) {
            // Thêm điều kiện tìm kiếm theo tiêu đề hoặc điểm đến
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('destination_text', 'like', '%' . $search . '%');
            });
            //     WHERE (
            //     title LIKE '%da-lat%'
            //     OR destination_text LIKE '%da-lat%'
            // )
        }

        // Lọc theo điểm đến (destination)
        if ($destination = $request->input('destination')) {
            if ($destination !== 'all') {
                $query->where('destination_text', 'like', '%' . $destination . '%');
            }
        }

        // Lọc theo loại tour (tour_type: domestic / international)
        $tourTypes = (array) $request->input('tour_type', []);
        $tourTypes = array_filter($tourTypes); // loại bỏ giá trị rỗng
        if (!empty($tourTypes)) {
            $query->whereIn('tour_type', $tourTypes);
        }

        // Lọc theo số ngày (duration)
        if ($duration = $request->input('duration')) {
            switch ($duration) {
                case '1-3':
                    $query->whereBetween('duration_days', [1, 3]);
                    break;
                case '4-6':
                    $query->whereBetween('duration_days', [4, 6]);
                    break;
                case '7plus':
                    $query->where('duration_days', '>=', 7);
                    break;
            }
        }

        // Lọc theo khoảng giá (dựa trên base_price_from)
        $priceMin = $request->input('price_min');
        $priceMax = $request->input('price_max');

        if ($priceMin !== null && $priceMin !== '') {
            $query->where('base_price_from', '>=', (float) str_replace(',', '', $priceMin));
        }

        if ($priceMax !== null && $priceMax !== '') {
            $query->where('base_price_from', '<=', (float) str_replace(',', '', $priceMax));
        }

        // Sắp xếp theo lựa chọn của người dùng
        $sort = $request->input('sort');

        switch ($sort) {
            case 'price_asc':
                // Giá từ thấp đến cao (dựa vào base_price_from)
                $query->orderBy('base_price_from', 'asc');
                break;

            case 'price_desc':
                // Giá từ cao xuống thấp
                $query->orderBy('base_price_from', 'desc');
                break;

            case 'rating_desc':
                // Đánh giá cao: dùng withAvg để order theo trung bình rating
                $query->withAvg('reviews', 'rating')
                    ->orderByDesc('reviews_avg_rating');
                break;

            case 'newest':
            default:
                // Mới nhất: sort theo thời gian tạo tour
                $query->orderByDesc('created_at');
                break;
        }

        $tours = $query->paginate(12)->withQueryString();

        $tours->getCollection()->transform(function ($tour) {
            // Ảnh đầu tiên
            $tour->main_image = $tour->images->first()
                ? asset('storage/' . $tour->images->first()->url)
                : asset('storage/image/bg.png');

            // Lịch khởi hành gần nhất còn chỗ
            $availableDeparture = $tour->departures
                ->where('start_date', '>=', now()->toDateString())
                ->sortBy('start_date')
                ->first();

            $tour->display_departure = $availableDeparture
                ? \Carbon\Carbon::parse($availableDeparture->start_date)->format('d/m/Y')
                : null;

            // Giá hiển thị: ưu tiên giá lịch khởi hành gần nhất, fallback về base_price_from
            $displayPrice = $availableDeparture
                ? $availableDeparture->price_adult
                : $tour->base_price_from;

            $tour->display_price = $displayPrice;
            // Giá gạch nếu base_price_from lớn hơn giá hiện tại (đang khuyến mãi)
            $tour->display_old_price = $displayPrice < $tour->base_price_from
                ? $tour->base_price_from
                : null;

            // Trạng thái chỗ
            if ($availableDeparture) {
                $remainingSeats = $availableDeparture->capacity_total - $availableDeparture->capacity_booked;

                if ($remainingSeats > 10) {
                    $tour->status_text = '✔ Còn chỗ';
                    $tour->status_class = 'bg-emerald-600';
                } elseif ($remainingSeats > 0) {
                    $tour->status_text = '⏳ Sắp hết chỗ';
                    $tour->status_class = 'bg-amber-500';
                } else {
                    $tour->status_text = '❌ Hết chỗ';
                    $tour->status_class = 'bg-red-600';
                }
            } else {
                $tour->status_text = '🔥 Nhiều người quan tâm';
                $tour->status_class = 'bg-pink-600';
            }

            // Số ngày số đêm
            $tour->duration_text = $tour->duration_days . 'N' . $tour->duration_nights . 'Đ';

            // Điểm đến
            $tour->destination_display = $tour->destination_text ?? 'Đang cập nhật';

            // Rating
            $tour->reviews_count = $tour->reviews->count();
            $tour->average_rating = $tour->reviews_count > 0
                ? round($tour->reviews->avg('rating'), 1)
                : 0;

            // Label rating
            if ($tour->average_rating >= 4.5) {
                $tour->rating_text = 'Tuyệt vời';
            } elseif ($tour->average_rating >= 4.0) {
                $tour->rating_text = 'Rất tốt';
            } elseif ($tour->average_rating >= 3.0) {
                $tour->rating_text = 'Tốt';
            } elseif ($tour->average_rating > 0) {
                $tour->rating_text = 'Bình thường';
            } else {
                $tour->rating_text = 'Chưa có đánh giá';
            }

            return $tour;
        });

        return view('tours.tours', compact('tours'));
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
        $tour->reviews_count = $tour->reviews->where('status', 'approved')->count();
        $tour->average_rating = $tour->reviews->where('status', 'approved')->avg('rating') ?? 0;
        // Reviews đã duyệt
        $reviews = $tour->reviews->where('status', 'approved')->values();

        // Danh sách lịch khởi hành (chỉ lấy các lịch còn mở / chưa quá hạn)
        $schedules = $tour->departures
            ->where('start_date', '>=', now()->toDateString())
            ->whereIn('status', ['open', 'sold_out'])
            ->sortBy('start_date')
            ->map(function ($dep) {
                $seatLeft = max($dep->capacity_total - $dep->capacity_booked, 0); // đảm bảo không âm

                return [
                    'id' => $dep->id,
                    'date' => $dep->start_date,
                    'seat_left' => $seatLeft,
                    'price_adult' => (int) $dep->price_adult,
                    'price_child' => (int) $dep->price_child,
                    'meeting_point' => $dep->meeting_point,
                ];
            })
            ->values();

        // Tour liên quan: cùng điểm đến (ưu tiên) hoặc cùng loại tour, loại trừ tour hiện tại
        $relatedQuery = Tours::with(['images', 'departures']) // Load sẵn quan hệ để dùng trong view
            ->where('status', 'published')
            ->where('id', '!=', $tour->id);

        if (!empty($tour->destination_text)) {
            $relatedQuery->where('destination_text', $tour->destination_text);
        } else {
            $relatedQuery->where('tour_type', $tour->tour_type);
        }

        $relatedTours = $relatedQuery
            ->orderByDesc('created_at')
            ->take(3)
            ->get()
            ->map(function ($related) {
                $related->main_image = $related->images->first()
                    ? asset('storage/' . $related->images->first()->url)
                    : asset('storage/image/logo.png');

                $availableDeparture = $related->departures
                    ->where('start_date', '>=', now()->toDateString())
                    ->sortBy('start_date')
                    ->first();

                $displayPrice = $availableDeparture
                    ? $availableDeparture->price_adult
                    : $related->base_price_from;

                $related->display_price = $displayPrice;

                return $related;
            });

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
            'reviewBooking',
            'schedules',
            'relatedTours',
        ));
    }

    public function checkout(Request $request)
    {
        // kiểm tra đăng nhập
        if (!Auth::check()) {
            return redirect()->route('signin')->with('error', 'Vui lòng đăng nhập để đặt tour');
        }
        $data = $request->validate([
            'tour_id' => ['required', 'exists:tours,id'],
            'schedule_id' => ['required', 'exists:tour_departures,id'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
        ], [], [
            'tour_id' => 'tour',
            'schedule_id' => 'lịch khởi hành',
            'adults' => 'số lượng người lớn',
            'children' => 'số lượng trẻ em',
            'contact_name' => 'họ tên liên hệ',
            'contact_phone' => 'số điện thoại liên hệ',
            'contact_email' => 'email liên hệ',
        ]);
        $contact_name = Auth::user()->name ?? 'Khách hàng';
        $contact_email = Auth::user()->email ?? null;
        $contact_phone = Auth::user()->phone ?? null;
        $tour = Tours::where('id', $data['tour_id'])
            ->where('status', 'published')
            ->firstOrFail();

        $departure = tour_departures::where('id', $data['schedule_id'])
            ->where('tour_id', $tour->id)
            ->firstOrFail();

        if ($departure->start_date < now()->toDateString()) {
            return back()->withErrors(['schedule_id' => 'Lịch khởi hành đã quá hạn'])->withInput();
        }

        if (!in_array($departure->status, ['open', 'sold_out'])) {
            return back()->withErrors(['schedule_id' => 'Lịch khởi hành không khả dụng'])->withInput();
        }

        $adults = (int) $data['adults'];
        $children = (int) ($data['children'] ?? 0);
        $totalGuests = $adults + $children;

        $remaining = $departure->capacity_total - $departure->capacity_booked;
        if ($totalGuests > $remaining) {
            return back()->withErrors([
                'schedule_id' => 'Số lượng khách vượt quá số chỗ còn lại (' . max($remaining, 0) . ' chỗ)',
            ])->withInput();
        }

        $priceAdult = (float) $departure->price_adult; 
        $priceChild = (float) $departure->price_child;

        $subtotal = $adults * $priceAdult + $children * $priceChild;
        $discountTotal = 0;
        $totalAmount = $subtotal - $discountTotal;

        $order = orders::create([
            'order_code' => 'OD' . now()->format('YmdHis') . rand(100, 999),
            'user_id' => Auth::id(),
            'contact_name' => $contact_name,
            'contact_phone' => $contact_phone,
            'contact_email' => $contact_email,
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'total_amount' => $totalAmount,
            'status' => 'pending',
        ]);

        Bookings::create([
            'order_id' => $order->id,
            'departure_id' => $departure->id,
            'note' => null,
            'status' => 'pending',
        ]);

        // Thêm chi tiết đơn hàng cho người lớn
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

        // Thêm chi tiết đơn hàng cho trẻ em (nếu có)
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
        $departure->increment('capacity_booked', $totalGuests); // Cập nhật số chỗ đã đặt

        return redirect()
            ->route('tours.show', $tour->slug)
            ->with('success', 'Đặt tour thành công! Chúng tôi sẽ liên hệ bạn để xác nhận và hướng dẫn thanh toán.');
    }
}
