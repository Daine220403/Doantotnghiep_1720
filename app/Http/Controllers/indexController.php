<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tours;
use App\Models\Bookings;
use App\Models\Reviews;
use App\Models\orders;
use App\Models\order_details;
use App\Models\tour_departures;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class indexController extends Controller
{
    public function contact()
    {
        return view('contact');
    }


    public function contactStore(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:20'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
            'preferred_contact' => ['nullable', 'in:phone,email,zalo'],
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'subject.required' => 'Vui lòng nhập chủ đề.',
            'message.required' => 'Vui lòng nhập nội dung liên hệ.',
            'message.min' => 'Nội dung liên hệ cần ít nhất 10 ký tự.',
        ]);

        ContactMessage::create([
            ...$validated,
            'status' => 'new',
            'ip_address' => $request->ip(), // Lưu địa chỉ IP của người gửi để tiện theo dõi và phân tích sau này
            'user_agent' => (string) $request->userAgent(), // Lưu user agent để biết người gửi dùng thiết bị gì, trình duyệt nào (cũng hữu ích cho việc phân tích sau này)
        ]);

        return redirect()
            ->route('contact')
            ->with('success', 'VieTravel đã nhận thông tin. Chúng tôi sẽ liên hệ lại sớm nhất.');
    }


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

        $upcomingTours = Tours::with([
            'images',
            'departures' => function ($query) {
                $query->whereDate('start_date', '>', now())
                    ->whereIn('status', ['draft', 'open', 'confirmed'])
                    ->orderBy('start_date');
            },
            'reviews'
        ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 'hidden')
            ->latest()
            ->take(4)
            ->get();

        return view('index', compact('tours', 'upcomingTours'));
    }


    public function tours(Request $request)
    {
        $locationFilters = $this->buildLocationFilters();
        $selectedTourScope = (string) $request->input('tour_scope', $request->input('scope', $request->input('type', '')));
        $selectedRegion = (string) $request->input('region', '');
        $selectedCity = (string) $request->input('city', '');
        $selectedScopeFilters = $selectedTourScope !== '' && $selectedTourScope !== 'all'
            ? ($locationFilters['scope_filters'][$selectedTourScope] ?? null)
            : null;

        if ($selectedScopeFilters && $selectedRegion !== '' && $selectedRegion !== 'all') {
            if (!array_key_exists($selectedRegion, $selectedScopeFilters['regions'])) {
                $selectedRegion = '';
            }
        }

        if ($selectedRegion !== '' && $selectedRegion !== 'all') {
            $selectedRegionCities = $selectedScopeFilters
                ? (($selectedScopeFilters['regions'][$selectedRegion]['cities'] ?? []))
                : ($locationFilters['cities_by_region'][$selectedRegion] ?? []);

            if ($selectedCity !== '' && $selectedCity !== 'all' && !in_array($selectedCity, $selectedRegionCities, true)) {
                $selectedCity = '';
            }
        } elseif ($selectedCity !== '' && $selectedCity !== 'all') {
            $availableCities = $selectedScopeFilters
                ? ($selectedScopeFilters['all_cities'] ?? [])
                : ($locationFilters['all_cities'] ?? []);

            if (!in_array($selectedCity, $availableCities, true)) {
                $selectedCity = '';
            }
        }

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

        if (in_array($selectedTourScope, ['domestic', 'international'], true)) {
            $query->where('tour_type', $selectedTourScope);
        }

        // Lọc theo vùng miền trước, sau đó lọc chi tiết theo thành phố
        if ($selectedRegion !== '' && $selectedRegion !== 'all') {
            $regionCities = $locationFilters['cities_by_region'][$selectedRegion] ?? [];

            if (!empty($regionCities)) {
                $query->where(function ($q) use ($regionCities) {
                    foreach ($regionCities as $city) {
                        $q->orWhere('destination_text', 'like', '%' . $city . '%');
                    }
                });
            }
        }

        if ($selectedCity !== '' && $selectedCity !== 'all') {
            $query->where('destination_text', 'like', '%' . $selectedCity . '%');
        }

        // Lọc theo ngày khởi hành
        if ($startDate = $request->input('start_date')) {
            $query->whereHas('departures', function ($q) use ($startDate) {
                $q->whereDate('start_date', $startDate)
                    ->whereIn('status', ['open', 'confirmed', 'sold_out']);
            });
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

        $priceRangeQuery = clone $query;
        $priceMinAvailable = 0;
        $priceMaxAvailable = (int) (((clone $query)->max('base_price_from')) ?? 0);
        $priceSliderStep = 500000;

        if ($priceMaxAvailable > 0) {
            $priceMaxAvailable = (int) (ceil($priceMaxAvailable / $priceSliderStep) * $priceSliderStep);
        } else {
            $priceMaxAvailable = 20000000;
        }

        if ($priceMinAvailable >= $priceMaxAvailable) {
            $priceMinAvailable = 0;
        }

        $requestedPriceMax = preg_replace('/[^0-9]/', '', (string) $request->input('price_max', ''));
        $selectedPriceMax = $requestedPriceMax !== ''
            ? (int) $requestedPriceMax
            : $priceMaxAvailable;

        $selectedPriceMax = max($priceMinAvailable, min($selectedPriceMax, $priceMaxAvailable));

        // Lọc theo mức giá tối đa (dựa trên base_price_from)
        if ($selectedPriceMax < $priceMaxAvailable) {
            $query->where('base_price_from', '<=', $selectedPriceMax);
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

        return view('tours.tours', [
            'tours' => $tours,
            'locationFilters' => $locationFilters,
            'priceFilter' => [
                'min' => $priceMinAvailable,
                'max' => $priceMaxAvailable,
                'selected_max' => $selectedPriceMax,
                'step' => $priceSliderStep,
            ],
            'selectedTourScope' => $selectedTourScope,
            'selectedRegion' => $selectedRegion,
            'selectedCity' => $selectedCity,
        ]);
    }

    private function buildLocationFilters(): array
    {
        $scopeFilters = [
            'domestic' => [
                'label' => 'Tour trong nước',
                'region_labels' => [
                    'mien-bac' => 'Miền Bắc',
                    'mien-trung' => 'Miền Trung',
                    'mien-nam' => 'Miền Nam',
                    'khac' => 'Khác',
                ],
                'regions' => [
                    'mien-bac' => ['label' => 'Miền Bắc', 'cities' => []],
                    'mien-trung' => ['label' => 'Miền Trung', 'cities' => []],
                    'mien-nam' => ['label' => 'Miền Nam', 'cities' => []],
                    'khac' => ['label' => 'Khác', 'cities' => []],
                ],
                'all_cities' => [],
            ],
            'international' => [
                'label' => 'Tour ngoài nước',
                'region_labels' => [
                    'khac' => 'Quốc tế',
                ],
                'regions' => [
                    'khac' => ['label' => 'Quốc tế', 'cities' => []],
                ],
                'all_cities' => [],
            ],
        ];

        $citiesByRegion = [
            'mien-bac' => [],
            'mien-trung' => [],
            'mien-nam' => [],
            'khac' => [],
        ];

        Tours::query()
            ->where('status', 'published')
            ->get(['tour_type', 'destination_text'])
            ->each(function ($tour) use (&$citiesByRegion, &$scopeFilters) {
                if (!$tour->destination_text) {
                    return;
                }

                $scopeKey = $tour->tour_type === 'international' ? 'international' : 'domestic';

                if (!isset($scopeFilters[$scopeKey])) {
                    return;
                }

                $scopeRegionKeys = array_keys($scopeFilters[$scopeKey]['regions']);

                foreach ($this->extractDestinationCities($tour->destination_text) as $city) {
                    $regionKey = $this->resolveRegionKey($city);
                    $citiesByRegion[$regionKey][$city] = $city;

                    if (!in_array($regionKey, $scopeRegionKeys, true)) {
                        $regionKey = 'khac';
                    }

                    $scopeFilters[$scopeKey]['regions'][$regionKey]['cities'][$city] = $city;
                    $scopeFilters[$scopeKey]['all_cities'][$city] = $city;
                }
            });

        $regions = [
            'mien-bac' => 'Miền Bắc',
            'mien-trung' => 'Miền Trung',
            'mien-nam' => 'Miền Nam',
        ];

        if (!empty($citiesByRegion['khac'])) {
            $regions['khac'] = 'Quốc tế / Khác';
        }

        foreach ($citiesByRegion as $regionKey => $cities) {
            $cities = array_values($cities);
            sort($cities, SORT_NATURAL | SORT_FLAG_CASE);
            $citiesByRegion[$regionKey] = $cities;
        }

        foreach ($scopeFilters as $scopeKey => $scopeData) {
            foreach ($scopeData['regions'] as $regionKey => $regionData) {
                $cities = array_values($regionData['cities']);
                sort($cities, SORT_NATURAL | SORT_FLAG_CASE);
                $scopeFilters[$scopeKey]['regions'][$regionKey]['cities'] = $cities;
            }

            $allCities = array_values($scopeData['all_cities']);
            sort($allCities, SORT_NATURAL | SORT_FLAG_CASE);
            $scopeFilters[$scopeKey]['all_cities'] = $allCities;

            $scopeFilters[$scopeKey]['regions'] = collect($scopeData['regions'])
                ->filter(fn ($regionData) => !empty($regionData['cities']))
                ->all();
        }

        $allCities = array_values(array_unique(array_merge(...array_values($citiesByRegion))));
        sort($allCities, SORT_NATURAL | SORT_FLAG_CASE);

        $availableTourScopes = collect($scopeFilters)
            ->filter(fn ($scopeData) => !empty($scopeData['all_cities']))
            ->mapWithKeys(fn ($scopeData, $scopeKey) => [$scopeKey => $scopeData['label']])
            ->all();

        return [
            'tour_scopes' => $availableTourScopes,
            'scope_filters' => $scopeFilters,
            'regions' => collect($regions)
                ->filter(fn ($label, $key) => !empty($citiesByRegion[$key]))
                ->all(),
            'cities_by_region' => $citiesByRegion,
            'all_cities' => $allCities,
        ];
    }

    private function extractDestinationCities(?string $destinationText): array
    {
        if (!$destinationText) {
            return [];
        }

        $cities = preg_split('/\s*[,;|\/]\s*/u', trim($destinationText)) ?: [];
        $cities = array_filter(array_map(fn ($city) => trim($city), $cities));

        if (empty($cities)) {
            return [trim($destinationText)];
        }

        return array_values(array_unique($cities));
    }

    private function resolveRegionKey(string $city): string
    {
        $normalizedCity = $this->normalizeLocationValue($city);

        foreach ($this->regionKeywordMap() as $regionKey => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($normalizedCity, $keyword)) {
                    return $regionKey;
                }
            }
        }

        return 'khac';
    }

    private function normalizeLocationValue(string $value): string
    {
        return Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9\s]/', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();
    }

    private function regionKeywordMap(): array
    {
        return [
            'mien-bac' => [
                'ha noi',
                'sapa',
                'sa pa',
                'ninh binh',
                'ha long',
                'cat ba',
                'cao bang',
                'ha giang',
                'moc chau',
                'yen tu',
            ],
            'mien-trung' => [
                'da nang',
                'hoi an',
                'hue',
                'nha trang',
                'quy nhon',
                'phu yen',
                'phan thiet',
                'mui ne',
                'da lat',
                'tay nguyen',
            ],
            'mien-nam' => [
                'phu quoc',
                'can tho',
                'my tho',
                'tien giang',
                'vung tau',
                'con dao',
                'soc trang',
                'ca mau',
                'an giang',
                'chau doc',
                'tp hcm',
                'ho chi minh',
                'sai gon',
            ],
        ];
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
            ->whereIn('status', ['open', 'sold_out', 'confirmed'])
            ->sortBy('start_date')
            ->map(function ($dep) {
                $seatLeft = max($dep->capacity_total - $dep->capacity_booked, 0); // đảm bảo không âm

                return [
                    'id' => $dep->id,
                    'date' => $dep->start_date,
                    'seat_left' => $seatLeft,
                    'price_adult' => (int) $dep->price_adult,
                    'price_child' => (int) $dep->price_child,
                    'price_infant' => (int) $dep->price_infant,
                    'price_youth' => (int) $dep->price_youth,
                    'single_room_surcharge' => (int) $dep->single_room_surcharge,
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


    /**
     * Trang đặt tour: chọn số lượng khách, nhập thông tin hành khách
     * Sau khi submit sẽ gửi sang paymentController@vnpay_payment
     */
    public function booking(Request $request, $slug)
    {
        // Bắt buộc đăng nhập trước khi đặt tour
        if (!Auth::check()) {
            return redirect()->route('signin')->with('error', 'Vui lòng đăng nhập để đặt tour');
        }

        $tour = Tours::with(['images', 'departures'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Danh sách lịch khởi hành hợp lệ
        $availableDepartures = $tour->departures
            ->where('start_date', '>=', now()->toDateString())
            ->whereIn('status', ['open', 'sold_out', 'confirmed'])
            ->sortBy('start_date');

        $scheduleId = (int) $request->query('schedule_id');

        if ($scheduleId) {
            $departure = $availableDepartures->firstWhere('id', $scheduleId);
        } else {
            $departure = $availableDepartures->first();
        }

        if (!$departure) {
            return redirect()->route('tours.show', $slug)
                ->with('error', 'Lịch khởi hành không khả dụng, vui lòng chọn ngày khác.');
        }

        $seatLeft = max($departure->capacity_total - $departure->capacity_booked, 0);

        return view('tours.booking', [
            'tour' => $tour,
            'departure' => $departure,
            'seatLeft' => $seatLeft,
        ]);
    }
}
