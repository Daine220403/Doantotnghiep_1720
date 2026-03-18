@php
    $title = 'Vie Travel - Chi tiết tour';
@endphp

@extends('layouts.app-guest')
<style>
    #tour-tab button[role="tab"][aria-selected="true"] {
        color: rgb(2 132 199);
        /* sky-600 */
        border-bottom-color: rgb(2 132 199);
    }
</style>
@section('content')
    <!-- PAGE HEADER / BREADCRUMB -->
    <section class="pt-28 pb-6 bg-white border-b border-gray-200">
        <div class="max-w-screen-xl mx-auto px-4">
            <nav class="text-sm text-gray-500 mb-3">
                <ol class="flex flex-wrap items-center gap-2">
                    <li><a href="{{ route('home') }}" class="hover:text-sky-600">Trang chủ</a></li>
                    <li class="opacity-60">/</li>
                    <li><a href="{{ route('tours') }}" class="hover:text-sky-600">Tours</a></li>
                    <li class="opacity-60">/</li>
                    <li class="text-gray-900 font-medium line-clamp-1">
                        {{ $tour->title }}
                    </li>
                </ol>
            </nav>

            <div class="flex flex-col gap-2">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
                    {{ $tour->title }}
                </h1>

                <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600">
                    <span class="inline-flex items-center gap-2">
                        ⭐ <b class="text-gray-900">{{ number_format($tour->average_rating, 1) }}</b>
                        <span>({{ $tour->reviews_count }} đánh giá)</span>
                    </span>

                    <span class="hidden md:inline text-gray-300">•</span>

                    <span class="inline-flex items-center gap-2">
                        <i class="fas fa-calendar-alt"></i> <span class="line-clamp-1">{{ $tour->destination_text }}</span>
                    </span>

                    <span class="hidden md:inline text-gray-300">•</span>

                    <span class="inline-flex items-center gap-2">
                        <i class="fas fa-clock"></i> {{ $tour->duration_days }} ngày {{ $tour->duration_nights }} đêm
                    </span>

                    <span class="hidden md:inline text-gray-300">•</span>

                    <span
                        class="inline-flex items-center gap-2 px-2 py-1 rounded-full text-xs font-semibold
                        {{ ($tour->tour_type ?? 'domestic') === 'international' ? 'bg-amber-50 text-amber-700' : 'bg-sky-50 text-sky-700' }}">
                        {{ ($tour->tour_type ?? 'domestic') === 'international' ? 'Tour quốc tế' : 'Tour trong nước' }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTENT -->
    <section class="py-8 bg-gray-50">
        <div class="max-w-screen-xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-12 gap-8">

            <!-- LEFT -->
            <div class="lg:col-span-8 space-y-6">

                <!-- GALLERY -->
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-2 p-2">
                        <div class="md:col-span-7">
                            @php
                                $coverImage = $tour->images->firstWhere('sort_order', 1) ?? $tour->images->first();
                            @endphp
                            <img src="{{ $coverImage ? asset('storage/' . $coverImage->url) : asset('storage/image/logo.png') }}"
                                class="w-full h-72 md:h-80 object-cover rounded-xl" alt="cover">
                        </div>
                        <div class="md:col-span-5 grid grid-cols-2 gap-2">
                            @foreach (array_slice($tour->images->toArray(), 0, 4) as $img)
                                <img src="{{ asset('storage/' . $img['url']) }}"
                                    class="w-full h-36 md:h-[156px] object-cover rounded-xl" alt="thumb">
                            @endforeach
                        </div>
                    </div>

                    <!-- QUICK INFO -->
                    <div class="px-5 pb-5">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div class="rounded-xl border border-gray-200 px-4 py-3">
                                <div class="text-xs text-gray-500">Khởi hành</div>
                                <div class="font-semibold text-gray-900">{{ $tour->departure_location }}</div>
                            </div>
                            <div class="rounded-xl border border-gray-200 px-4 py-3">
                                <div class="text-xs text-gray-500">Phương tiện</div>
                                @if ($tour->transport === 'bus')
                                    <div class="font-semibold text-gray-900">Xe khách</div>
                                @elseif ($tour->transport === 'plane')
                                    <div class="font-semibold text-gray-900">Máy bay</div>
                                @elseif ($tour->transport === 'train')
                                    <div class="font-semibold text-gray-900">Tàu hỏa</div>
                                @else
                                    <div class="font-semibold text-gray-900">Xe du lịch</div>
                                @endif
                            </div>
                            <div class="rounded-xl border border-gray-200 px-4 py-3">
                                <div class="text-xs text-gray-500">Số chỗ</div>
                                <div class="font-semibold text-gray-900">{{ $tour->seat ?? 'Còn 12' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== FLOWBITE TABS (Chuẩn Active/Inactive) ===== --}}

                <div class="mb-4 border-b border-gray-200">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="tour-tab"
                        data-tabs-toggle="#tour-tab-content" role="tablist">

                        {{-- LỊCH TRÌNH (ACTIVE MẶC ĐỊNH) --}}
                        <li class="me-2" role="presentation">
                            <button class="inline-block p-4 border-b-2 rounded-t-lg " id="itinerary-tab"
                                data-tabs-target="#itinerary" type="button" role="tab" aria-controls="itinerary"
                                aria-selected="true">
                                Lịch trình
                            </button>
                        </li>

                        {{-- TỔNG QUAN --}}
                        <li class="me-2" role="presentation">
                            <button
                                class="inline-block p-4 border-b-2 rounded-t-lg border-transparent hover:text-gray-600 hover:border-gray-300"
                                id="overview-tab" data-tabs-target="#overview" type="button" role="tab"
                                aria-controls="overview" aria-selected="false">
                                Tổng quan tour
                            </button>
                        </li>

                        {{-- BAO GỒM --}}
                        <li class="me-2" role="presentation">
                            <button
                                class="inline-block p-4 border-b-2 rounded-t-lg border-transparent hover:text-gray-600 hover:border-gray-300"
                                id="included-tab" data-tabs-target="#included" type="button" role="tab"
                                aria-controls="included" aria-selected="false">
                                Bao gồm
                            </button>
                        </li>

                        {{-- KHÔNG BAO GỒM --}}
                        <li role="presentation">
                            <button
                                class="inline-block p-4 border-b-2 rounded-t-lg border-transparent hover:text-gray-600 hover:border-gray-300"
                                id="excluded-tab" data-tabs-target="#excluded" type="button" role="tab"
                                aria-controls="excluded" aria-selected="false">
                                Không bao gồm
                            </button>
                        </li>
                    </ul>
                </div>

                <div id="tour-tab-content">
                    {{-- ================= LỊCH TRÌNH ================= --}}
                    <div class="p-4 rounded-lg bg-gray-50" id="itinerary" role="tabpanel" aria-labelledby="itinerary-tab">

                        <div class="flex items-center justify-between gap-3 mb-4">
                            <h2 class="text-lg font-bold text-gray-900">Lịch trình</h2>
                            <span class="text-xs text-gray-500">* Có thể thay đổi tùy thời tiết</span>
                        </div>

                        <div class="space-y-4">
                            @foreach ($tour->itineraries as $i => $item)
                                <div class="flex gap-4">
                                    <div class="flex flex-col items-center">
                                        <div
                                            class="w-9 h-9 rounded-full bg-sky-600 text-white flex items-center justify-center font-bold">
                                            {{ $i + 1 }}
                                        </div>
                                        @if (!$loop->last)
                                            <div class="w-px flex-1 bg-gray-200 mt-2"></div>
                                        @endif
                                    </div>

                                    <div class="flex-1 pb-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span
                                                class="text-xs font-semibold px-2 py-1 rounded-full bg-sky-50 text-sky-700">
                                                {{ $item['day_no'] }}
                                            </span>
                                            <h3 class="font-semibold text-gray-900">{{ $item['title'] }}</h3>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-2">{{ $item['content'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ================= TỔNG QUAN ================= --}}
                    <div class="hidden p-4 rounded-lg bg-gray-50" id="overview" role="tabpanel"
                        aria-labelledby="overview-tab">

                        <h2 class="text-lg font-bold text-gray-900 mb-3">Tổng quan tour</h2>
                        <div class="prose max-w-none text-gray-700">
                            {!! $tour->description !!}
                        </div>
                    </div>

                    {{-- ================= BAO GỒM ================= --}}
                    <div class="hidden p-4 rounded-lg bg-gray-50" id="included" role="tabpanel"
                        aria-labelledby="included-tab">

                        <h2 class="text-lg font-bold text-gray-900 mb-3">Bao gồm</h2>
                        <ul class="space-y-2 text-sm text-gray-700">
                            @foreach ($tour->policies as $policy)
                                @if ($policy->type === 'include')
                                    <li class="flex gap-2">
                                        <span class="text-green-600">✔</span>
                                        <span>{{ $policy->content }}</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>

                    {{-- ================= KHÔNG BAO GỒM ================= --}}
                    <div class="hidden p-4 rounded-lg bg-gray-50" id="excluded" role="tabpanel"
                        aria-labelledby="excluded-tab">

                        <h2 class="text-lg font-bold text-gray-900 mb-3">Không bao gồm</h2>
                        <ul class="space-y-2 text-sm text-gray-700">
                            @foreach ($tour->policies as $policy)
                                @if ($policy->type === 'exclude')
                                    <li class="flex gap-2">
                                        <span class="text-red-600">✖</span>
                                        <span>{{ $policy->content }}</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>

                </div>

                <!-- MAP -->
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <h2 class="text-lg font-bold text-gray-900 mb-3">Bản đồ</h2>
                    <div class="rounded-2xl overflow-hidden border border-gray-200">
                        {{-- Anh thay iframe theo tour thực tế (lat/lng hoặc embed link) --}}
                        <iframe class="w-full h-72" src="https://www.google.com/maps?q=Da%20Lat&output=embed"
                            loading="lazy"></iframe>
                    </div>
                </div>

                <!-- REVIEWS -->
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    @php
                        // Danh sách review đã duyệt
                        $approvedReviews = $tour->reviews ?? collect();

                        $reviewCount = $approvedReviews->count();
                        $avgRating = $reviewCount > 0 ? round($approvedReviews->avg('rating'), 1) : 0;

                        // Điều kiện nghiệp vụ
                        $isLoggedIn = auth()->check();

                        // User đã từng đặt và hoàn thành tour này chưa
                        // Biến này nên truyền từ controller sang sẽ tốt hơn
                        $canReview = $canReview ?? false;

                        // User đã review tour này chưa
                        $hasReviewed = $hasReviewed ?? false;

                        // Booking hợp lệ để review
                        $reviewBooking = $reviewBooking ?? null;
                    @endphp

                    <!-- Header -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Đánh giá khách hàng</h2>
                            <p class="text-sm text-gray-500 mt-1">
                                Chia sẻ trải nghiệm thực tế của khách đã tham gia tour
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <div class="text-2xl font-bold text-gray-900">{{ $avgRating }}</div>
                                <div class="text-sm text-gray-500">{{ $reviewCount }} đánh giá</div>
                            </div>
                            <div class="text-amber-500 text-lg">
                                {{ str_repeat('★', (int) round($avgRating)) }}{{ str_repeat('☆', 5 - (int) round($avgRating)) }}
                            </div>
                        </div>
                    </div>

                    <!-- Nút viết đánh giá -->
                    <div class="mb-5">
                        @if ($isLoggedIn && $canReview && !$hasReviewed)
                            <a href="#review-form"
                                class="inline-flex items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-700 hover:bg-sky-100">
                                Viết đánh giá
                            </a>
                        @elseif (!$isLoggedIn)
                            <a href="{{ route('signin') }}"
                                class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                                Đăng nhập để đánh giá
                            </a>
                        @endif
                    </div>

                    <!-- Danh sách review -->
                    @if ($reviewCount > 0)
                        <div class="space-y-4">
                            @foreach ($approvedReviews as $review)
                                <div class="rounded-2xl border border-gray-200 bg-white p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="font-semibold text-gray-900">
                                                {{ $review->user->name ?? 'Khách hàng' }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ $review->created_at ? $review->created_at->diffForHumans() : '' }}
                                            </div>
                                        </div>

                                        <div class="text-sm text-amber-500 shrink-0">
                                            {{ str_repeat('★', (int) $review->rating) }}{{ str_repeat('☆', 5 - (int) $review->rating) }}
                                        </div>
                                    </div>

                                    @if (!empty($review->content))
                                        <p class="mt-3 text-sm leading-6 text-gray-700">
                                            {{ $review->content }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center">
                            <p class="text-sm text-gray-500">Hiện chưa có đánh giá nào cho tour này.</p>
                        </div>
                    @endif

                    <!-- FORM REVIEW -->
                    <div id="review-form" class="mt-6 border-t border-gray-200 pt-6">
                        @if (!$isLoggedIn)
                            <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-4">
                                <p class="text-sm text-yellow-800">
                                    Bạn cần đăng nhập để gửi đánh giá cho tour này.
                                </p>
                            </div>
                        @elseif ($hasReviewed)
                            <div class="rounded-2xl border border-green-200 bg-green-50 p-4">
                                <p class="text-sm text-green-800 font-medium">
                                    Bạn đã gửi đánh giá cho tour này rồi.
                                </p>
                            </div>
                        @elseif (!$canReview)
                            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4">
                                <p class="text-sm text-blue-800">
                                    Bạn chỉ có thể đánh giá sau khi đã đặt và hoàn thành tour này.
                                </p>
                            </div>
                        @else
                            <h3 class="mb-3 font-bold text-gray-900">Gửi đánh giá của bạn</h3>

                            <form action="{{ route('reviews.store') }}" method="POST" class="space-y-4">
                                @csrf

                                {{-- tour_id + booking_id --}}
                                <input type="hidden" name="tour_id" value="{{ $tour->id }}">
                                <input type="hidden" name="booking_id" value="{{ $reviewBooking?->id }}">

                                <div class="rounded-xl bg-gray-50 px-4 py-3 text-sm text-gray-700">
                                    Người đánh giá:
                                    <span class="font-semibold">{{ auth()->user()->name }}</span>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-gray-700">Số sao</label>
                                    <select name="rating"
                                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-sky-500 focus:ring-4 focus:ring-sky-100"
                                        required>
                                        <option value="">-- Chọn số sao --</option>
                                        <option value="5" {{ old('rating') == 5 ? 'selected' : '' }}>5 sao - Rất hài
                                            lòng</option>
                                        <option value="4" {{ old('rating') == 4 ? 'selected' : '' }}>4 sao - Hài lòng
                                        </option>
                                        <option value="3" {{ old('rating') == 3 ? 'selected' : '' }}>3 sao - Bình
                                            thường</option>
                                        <option value="2" {{ old('rating') == 2 ? 'selected' : '' }}>2 sao - Chưa tốt
                                        </option>
                                        <option value="1" {{ old('rating') == 1 ? 'selected' : '' }}>1 sao - Không
                                            hài lòng</option>
                                    </select>
                                    @error('rating')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-semibold text-gray-700">Nội dung đánh giá</label>
                                    <textarea name="content" rows="4"
                                        placeholder="Chia sẻ trải nghiệm của bạn về lịch trình, hướng dẫn viên, khách sạn, bữa ăn..."
                                        class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-sky-500 focus:ring-4 focus:ring-sky-100">{{ old('content') }}</textarea>
                                    @error('content')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit"
                                    class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 focus:ring-4 focus:ring-blue-200">
                                    Gửi đánh giá
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- RELATED TOURS -->
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <h2 class="text-lg font-bold text-gray-900">Tour liên quan</h2>
                        <a href="{{ route('tours') }}" class="text-sm font-semibold text-sky-600 hover:text-sky-700">Xem
                            tất cả</a>
                    </div>

                    @if ($relatedTours->isEmpty())
                        <p class="text-sm text-gray-500">Hiện chưa có tour liên quan phù hợp.</p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach ($relatedTours as $rt)
                                <a href="{{ route('tours.show', $rt->slug) }}"
                                    class="group rounded-2xl border border-gray-200 overflow-hidden bg-white hover:shadow-md transition">
                                    <img src="{{ $rt->main_image }}" class="w-full h-36 object-cover" alt="related">
                                    <div class="p-4">
                                        <div
                                            class="font-semibold text-gray-900 line-clamp-2 group-hover:text-sky-700 transition">
                                            {{ $rt->title }}
                                        </div>
                                        <div class="mt-2 flex items-center justify-between">
                                            <span class="text-sm text-gray-500">Từ</span>
                                            <span class="font-bold text-sky-700">
                                                {{ number_format($rt->display_price, 0, ',', '.') }}đ
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- RIGHT / BOOKING SIDEBAR -->
            <aside class="lg:col-span-4">
                {{-- sticky : dùng để neo --}}
                <div class="top-24 space-y-4">
                    <div class="bg-white rounded-2xl border border-gray-200 p-5">

                        <form action="{{ route('tours.checkout') }}" method="POST" class="mt-6 space-y-4">
                            @csrf

                            <input type="hidden" name="tour_id" value="{{ $tour->id }}">
                            <!-- CHỌN NGÀY -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700">Chọn ngày khởi hành</label>
                                <select id="scheduleSelect" name="schedule_id"
                                    class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                                    @forelse ($schedules as $s)
                                        <option value="{{ $s['id'] }}" data-adult="{{ $s['price_adult'] }}"
                                            data-child="{{ $s['price_child'] }}"
                                            data-meeting="{{ $s['meeting_point'] }}">
                                            {{ \Carbon\Carbon::parse($s['date'])->format('d/m/Y') }}
                                            — Còn {{ $s['seat_left'] }} chỗ
                                        </option>
                                        {{-- <input type="hidden" name="schedule_{{ $s['id'] }}" value="{{ $s['id'] }}"> --}}
                                    @empty
                                        <option value="" disabled>Hiện chưa có lịch khởi hành phù hợp</option>
                                    @endforelse
                                </select>
                            </div>
                            <!-- GIÁ THEO NGƯỜI -->
                            <div class="space-y-4">
                                <div>
                                    <div class="text-sm text-gray-500">Người lớn (trên 11 tuổi)</div>
                                    <div id="adultPrice" class="text-xl font-bold text-gray-900">0 VND</div>
                                </div>

                                <div>
                                    <div class="text-sm text-gray-500">Trẻ em (2–11 tuổi)</div>
                                    <div id="childPrice" class="text-xl font-bold text-gray-900">0 VND</div>
                                </div>
                            </div>

                            {{-- Điểm tập trung --}}
                            @php
                                $firstSchedule = $schedules->first();
                                $initialMeetingPoint =
                                    $firstSchedule['meeting_point'] ?? ($tour->departure_location ?? 'Đang cập nhật');
                            @endphp
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Điểm tập trung</span>
                                    <b id="meetingPointValue" class="text-gray-900">{{ $initialMeetingPoint }}</b>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    * Thông tin có thể thay đổi, vui lòng kiểm tra lại khi đặt tour
                                </div>
                            </div>

                            {{-- note --}}
                            <div class="text-xs text-gray-500 mt-1">
                                <textarea name="note"
                                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none"
                                    rows="4"></textarea>
                            </div>

                            <!-- SỐ LƯỢNG -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-sm font-semibold text-gray-700">Người lớn</label>
                                    <input id="adultQty" type="number" name="adults" min="1" value="1"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-gray-700">Trẻ em</label>
                                    <input id="childQty" type="number" name="children" min="0" value="0"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                                </div>
                            </div>

                            <!-- TẠM TÍNH -->
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Tạm tính</span>
                                    <b id="totalPrice" class="text-lg text-gray-900">0 VND</b>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    * Giá thay đổi theo ngày & số lượng
                                </div>
                            </div>

                            <!-- ACTION -->
                            {{-- kiểm tra đăng nhập trước khi submit --}}
                            @if (Auth::check() && Auth::user()->role === 'customer')
                                <button type="submit"
                                    class="w-full rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 focus:ring-4 focus:ring-blue-200">
                                    Đặt tour ngay
                                </button>
                            @else
                                <button type="button"
                                    class="w-full rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 focus:ring-4 focus:ring-blue-200"
                                    onclick="alert('Vui lòng đăng nhập trước khi đặt tour!')">
                                    Đặt tour ngay
                                </button>
                            @endif
                        </form>
                    </div>
                </div>
            </aside>

            <!-- SCRIPT TÍNH GIÁ -->
            <script>
                const scheduleSelect = document.getElementById('scheduleSelect');
                const adultQty = document.getElementById('adultQty');
                const childQty = document.getElementById('childQty');

                const adultPriceEl = document.getElementById('adultPrice');
                const childPriceEl = document.getElementById('childPrice');
                const totalPriceEl = document.getElementById('totalPrice');
                const meetingPointEl = document.getElementById('meetingPointValue');

                const defaultMeetingPoint = @json($tour->departure_location ?? 'Đang cập nhật');

                function formatVND(number) {
                    return new Intl.NumberFormat('vi-VN').format(number) + ' VND';
                }

                function updatePrice() {
                    if (!scheduleSelect || scheduleSelect.selectedIndex === -1) {
                        adultPriceEl.innerText = formatVND(0);
                        childPriceEl.innerText = formatVND(0);
                        totalPriceEl.innerText = formatVND(0);
                        if (meetingPointEl) {
                            meetingPointEl.innerText = defaultMeetingPoint;
                        }
                        return;
                    }

                    const selected = scheduleSelect.options[scheduleSelect.selectedIndex];

                    const priceAdult = parseInt(selected.dataset.adult);
                    const priceChild = parseInt(selected.dataset.child);

                    const adults = parseInt(adultQty.value) || 0;
                    const children = parseInt(childQty.value) || 0;

                    adultPriceEl.innerText = formatVND(priceAdult);
                    childPriceEl.innerText = formatVND(priceChild);
                    totalPriceEl.innerText = formatVND(
                        (priceAdult * adults) + (priceChild * children)
                    );

                    if (meetingPointEl) {
                        const meeting = selected.dataset.meeting || defaultMeetingPoint;
                        meetingPointEl.innerText = meeting;
                    }
                }

                scheduleSelect.addEventListener('change', updatePrice);
                adultQty.addEventListener('input', updatePrice);
                childQty.addEventListener('input', updatePrice);

                updatePrice(); // init
            </script>

        </div>
    </section>
@endsection
