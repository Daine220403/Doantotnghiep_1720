@php
    $title = 'Vie Travel - Chi tiết tour';
@endphp

@extends('layouts.app-guest')

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
                        {{ $tour->name ?? 'Tour Đà Lạt 3N2Đ - Săn mây & check-in' }}
                    </li>
                </ol>
            </nav>

            <div class="flex flex-col gap-2">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
                    {{ $tour->name ?? 'Tour Đà Lạt 3N2Đ - Săn mây & check-in' }}
                </h1>

                <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600">
                    <span class="inline-flex items-center gap-2">
                        ⭐ <b class="text-gray-900">{{ $tour->rating ?? '4.8' }}</b>
                        <span>({{ $tour->reviews_count ?? 128 }} đánh giá)</span>
                    </span>

                    <span class="hidden md:inline text-gray-300">•</span>

                    <span class="inline-flex items-center gap-2">
                        📍 <span class="line-clamp-1">{{ $tour->destination ?? 'Đà Lạt, Lâm Đồng' }}</span>
                    </span>

                    <span class="hidden md:inline text-gray-300">•</span>

                    <span class="inline-flex items-center gap-2">
                        ⏱ {{ $tour->duration ?? '3 ngày 2 đêm' }}
                    </span>

                    <span class="hidden md:inline text-gray-300">•</span>

                    <span
                        class="inline-flex items-center gap-2 px-2 py-1 rounded-full text-xs font-semibold
                        {{ ($tour->type ?? 'domestic') === 'international' ? 'bg-amber-50 text-amber-700' : 'bg-sky-50 text-sky-700' }}">
                        {{ ($tour->type ?? 'domestic') === 'international' ? 'Tour quốc tế' : 'Tour trong nước' }}
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
                            <img src="{{ $tour->cover_image ?? asset('storage/image/logo.png') }}"
                                class="w-full h-72 md:h-80 object-cover rounded-xl" alt="cover">
                        </div>
                        <div class="md:col-span-5 grid grid-cols-2 gap-2">
                            @php
                                $imgs = $tour->images ?? [
                                    asset('storage/image/logo.png'),
                                    asset('storage/image/logo.png'),
                                    asset('storage/image/logo.png'),
                                    asset('storage/image/logo.png'),
                                ];
                            @endphp

                            @foreach (array_slice($imgs, 0, 4) as $img)
                                <img src="{{ $img }}" class="w-full h-36 md:h-[156px] object-cover rounded-xl"
                                    alt="thumb">
                            @endforeach
                        </div>
                    </div>

                    <!-- QUICK INFO -->
                    <div class="px-5 pb-5">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div class="rounded-xl border border-gray-200 px-4 py-3">
                                <div class="text-xs text-gray-500">Khởi hành</div>
                                <div class="font-semibold text-gray-900">{{ $tour->departure ?? 'TP.HCM' }}</div>
                            </div>
                            <div class="rounded-xl border border-gray-200 px-4 py-3">
                                <div class="text-xs text-gray-500">Phương tiện</div>
                                <div class="font-semibold text-gray-900">{{ $tour->transport ?? 'Xe du lịch' }}</div>
                            </div>
                            <div class="rounded-xl border border-gray-200 px-4 py-3">
                                <div class="text-xs text-gray-500">Khách sạn</div>
                                <div class="font-semibold text-gray-900">{{ $tour->hotel ?? '3★' }}</div>
                            </div>
                            <div class="rounded-xl border border-gray-200 px-4 py-3">
                                <div class="text-xs text-gray-500">Số chỗ</div>
                                <div class="font-semibold text-gray-900">{{ $tour->seat ?? 'Còn 12' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OVERVIEW -->
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <h2 class="text-lg font-bold text-gray-900 mb-3">Tổng quan tour</h2>
                    <div class="prose max-w-none text-gray-700">
                        {!! $tour->description ??
                            '
                                                                                                                                                    <p>
                                                                                                                                                        Hành trình đưa anh khám phá Đà Lạt với lịch trình tối ưu: săn mây, check-in các điểm hot,
                                                                                                                                                        trải nghiệm ẩm thực và nghỉ dưỡng. Phù hợp cho gia đình, nhóm bạn, cặp đôi.
                                                                                                                                                    </p>
                                                                                                                                                    <ul>
                                                                                                                                                        <li>Check-in: Đồi chè Cầu Đất, cổng trời Bali, hồ Tuyền Lâm</li>
                                                                                                                                                        <li>Ẩm thực: lẩu gà lá é, bánh căn, sữa đậu nành</li>
                                                                                                                                                        <li>Lịch trình linh hoạt, hướng dẫn viên tận tâm</li>
                                                                                                                                                    </ul>
                                                                                                                                                ' !!}
                    </div>
                </div>

                <!-- ITINERARY -->
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <h2 class="text-lg font-bold text-gray-900">Lịch trình</h2>
                        <span class="text-xs text-gray-500">* Có thể thay đổi tùy thời tiết</span>
                    </div>

                    @php
                        $itinerary = $tour->itinerary ?? [
                            [
                                'day' => 'Ngày 1',
                                'title' => 'TP.HCM → Đà Lạt | Check-in & ăn tối',
                                'content' => 'Di chuyển, nhận phòng, tham quan chợ đêm.',
                            ],
                            [
                                'day' => 'Ngày 2',
                                'title' => 'Săn mây Cầu Đất | Cafe | Hồ Tuyền Lâm',
                                'content' => 'Săn mây sáng sớm, trải nghiệm cafe, tham quan hồ.',
                            ],
                            [
                                'day' => 'Ngày 3',
                                'title' => 'Mua sắm đặc sản | Trở về',
                                'content' => 'Tự do mua sắm, trả phòng, về TP.HCM.',
                            ],
                        ];
                    @endphp

                    <div class="space-y-4">
                        @foreach ($itinerary as $i => $item)
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
                                        <span class="text-xs font-semibold px-2 py-1 rounded-full bg-sky-50 text-sky-700">
                                            {{ $item['day'] }}
                                        </span>
                                        <h3 class="font-semibold text-gray-900">{{ $item['title'] }}</h3>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-2">{{ $item['content'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- INCLUDED / EXCLUDED -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl border border-gray-200 p-5">
                        <h2 class="text-base font-bold text-gray-900 mb-3">Bao gồm</h2>
                        <ul class="space-y-2 text-sm text-gray-700">
                            @foreach ($tour->included ?? ['Xe đưa đón', 'Khách sạn', 'Vé tham quan', 'Ăn uống theo chương trình', 'Hướng dẫn viên'] as $x)
                                <li class="flex gap-2"><span class="text-green-600">✔</span>
                                    <span>{{ $x }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-200 p-5">
                        <h2 class="text-base font-bold text-gray-900 mb-3">Không bao gồm</h2>
                        <ul class="space-y-2 text-sm text-gray-700">
                            @foreach ($tour->excluded ?? ['Chi phí cá nhân', 'VAT', 'Tiền tip (tùy chọn)'] as $x)
                                <li class="flex gap-2"><span class="text-red-600">✖</span> <span>{{ $x }}</span>
                                </li>
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
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <h2 class="text-lg font-bold text-gray-900">Đánh giá</h2>
                        <a href="#review-form" class="text-sm font-semibold text-sky-600 hover:text-sky-700">Viết đánh
                            giá</a>
                    </div>

                    @php
                        $reviews = $reviews ?? [
                            [
                                'name' => 'Ngọc Anh',
                                'rating' => 5,
                                'time' => '2 ngày trước',
                                'content' => 'Tour rất ok, HDV nhiệt tình, lịch trình hợp lý.',
                            ],
                            [
                                'name' => 'Tuấn',
                                'rating' => 4,
                                'time' => '1 tuần trước',
                                'content' => 'Khách sạn sạch, đồ ăn ổn, hơi mệt đoạn di chuyển.',
                            ],
                        ];
                    @endphp

                    <div class="space-y-4">
                        @foreach ($reviews as $r)
                            <div class="p-4 rounded-2xl border border-gray-200 bg-white">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $r['name'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $r['time'] }}</div>
                                    </div>
                                    <div class="text-sm text-amber-500">
                                        {{ str_repeat('★', (int) $r['rating']) }}{{ str_repeat('☆', 5 - (int) $r['rating']) }}
                                    </div>
                                </div>
                                <p class="text-sm text-gray-700 mt-2">{{ $r['content'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <!-- REVIEW FORM (UI only) -->
                    <div id="review-form" class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="font-bold text-gray-900 mb-3">Gửi đánh giá</h3>
                        <form action="#" method="POST" class="space-y-3">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <input type="text" name="name" placeholder="Họ tên"
                                    class="rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                                <select name="rating"
                                    class="rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                                    <option value="5">5 sao</option>
                                    <option value="4">4 sao</option>
                                    <option value="3">3 sao</option>
                                    <option value="2">2 sao</option>
                                    <option value="1">1 sao</option>
                                </select>
                            </div>
                            <textarea name="content" rows="3" placeholder="Nội dung đánh giá..."
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none"></textarea>
                            <button type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 focus:ring-4 focus:ring-blue-200">
                                Gửi đánh giá
                            </button>
                        </form>
                    </div>
                </div>

                <!-- RELATED TOURS -->
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <h2 class="text-lg font-bold text-gray-900">Tour liên quan</h2>
                        <a href="{{ route('tours') }}" class="text-sm font-semibold text-sky-600 hover:text-sky-700">Xem
                            tất cả</a>
                    </div>

                    @php
                        $relatedTours = $relatedTours ?? [
                            [
                                'name' => 'Tour Nha Trang 3N2Đ',
                                'price' => 2890000,
                                'img' => asset('storage/image/logo.png'),
                            ],
                            [
                                'name' => 'Tour Phú Quốc 3N2Đ',
                                'price' => 3590000,
                                'img' => asset('storage/image/logo.png'),
                            ],
                            ['name' => 'Tour Sapa 3N2Đ', 'price' => 3290000, 'img' => asset('storage/image/logo.png')],
                        ];
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach ($relatedTours as $rt)
                            <a href="#"
                                class="group rounded-2xl border border-gray-200 overflow-hidden bg-white hover:shadow-md transition">
                                <img src="{{ $rt['img'] }}" class="w-full h-36 object-cover" alt="related">
                                <div class="p-4">
                                    <div
                                        class="font-semibold text-gray-900 line-clamp-2 group-hover:text-sky-700 transition">
                                        {{ $rt['name'] }}
                                    </div>
                                    <div class="mt-2 flex items-center justify-between">
                                        <span class="text-sm text-gray-500">Từ</span>
                                        <span class="font-bold text-sky-700">
                                            {{ number_format($rt['price'], 0, ',', '.') }}đ
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- RIGHT / BOOKING SIDEBAR -->
            <aside class="lg:col-span-4">
                <div class="sticky top-28 space-y-4">
                    <div class="bg-white rounded-2xl border border-gray-200 p-5">

                        <form action="#" method="POST" class="mt-6 space-y-4">
                            @csrf

                            <!-- CHỌN NGÀY -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700">Chọn ngày khởi hành</label>
                                <select id="scheduleSelect" name="schedule_id"
                                    class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">

                                    @php
                                        $schedules = $schedules ?? [
                                            [
                                                'id' => 1,
                                                'date' => '2026-02-19',
                                                'seat_left' => 12,
                                                'price_adult' => 17990000,
                                                'price_child' => 14617500,
                                            ],
                                            [
                                                'id' => 2,
                                                'date' => '2026-02-21',
                                                'seat_left' => 8,
                                                'price_adult' => 18990000,
                                                'price_child' => 14990000,
                                            ],
                                        ];
                                    @endphp

                                    @foreach ($schedules as $s)
                                        <option value="{{ $s['id'] }}" data-adult="{{ $s['price_adult'] }}"
                                            data-child="{{ $s['price_child'] }}">
                                            {{ \Carbon\Carbon::parse($s['date'])->format('d/m/Y') }}
                                            — Còn {{ $s['seat_left'] }} chỗ
                                        </option>
                                    @endforeach
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
                            <button type="submit"
                                class="w-full rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 focus:ring-4 focus:ring-blue-200">
                                Đặt tour ngay
                            </button>

                            <button type="button"
                                class="w-full rounded-xl bg-white border border-gray-200 px-5 py-3 text-sm font-semibold text-gray-900 hover:bg-gray-50 focus:ring-4 focus:ring-gray-200">
                                Liên hệ tư vấn
                            </button>
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

                function formatVND(number) {
                    return new Intl.NumberFormat('vi-VN').format(number) + ' VND';
                }

                function updatePrice() {
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
                }

                scheduleSelect.addEventListener('change', updatePrice);
                adultQty.addEventListener('input', updatePrice);
                childQty.addEventListener('input', updatePrice);

                updatePrice(); // init
            </script>

        </div>
    </section>
@endsection
