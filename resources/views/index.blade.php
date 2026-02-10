@php
    $title = 'Vie Travel - Nâng tầm trải nghiệm';
@endphp
@extends('layouts.guest')
@section('content')
    <!-- HERO -->
    <section class="relative pt-10 overflow-hidden">
        <!-- Background -->
        <div class="absolute inset-0">
            <img src="{{ asset('storage/image/bg.png') }}" class="w-full h-full object-cover opacity-100" alt="Vietnam Travel">
            <div class="absolute inset-0 bg-gradient-to-r from-white/95 via-white/85 to-white/60"></div>
        </div>

        <!-- Content -->
        <div class="relative max-w-screen-xl mx-auto px-4 py-16">
            <div class="grid lg:grid-cols-12 gap-10 items-center">

                <!-- LEFT -->
                <div class="lg:col-span-6">
                    <span
                        class="inline-flex items-center rounded-full bg-sky-50 text-sky-600 px-4 py-1.5 text-sm font-medium">
                        ✈️ Trải nghiệm – An toàn – Tiết kiệm
                    </span>

                    <h1 class="mt-4 text-4xl md:text-5xl font-extrabold leading-tight">
                        Khám phá Việt Nam & Thế giới
                        <span class="text-sky-600">cùng VieTravel</span>
                    </h1>

                    <p class="mt-4 text-lg text-gray-700 leading-relaxed">
                        Hàng trăm tour trong nước & quốc tế, lịch trình rõ ràng,
                        giá minh bạch, hỗ trợ 24/7 cho mọi hành trình của bạn.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('tours', ['type' => 'domestic']) }}"
                            class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold
                            hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 transition">
                            Khám phá tour trong nước
                        </a>

                        <a href="{{ route('tours', ['type' => 'international']) }}"
                            class="inline-flex items-center gap-2 bg-sky-500 text-white px-6 py-3 rounded-lg font-semibold
                        hover:bg-sky-600 focus:ring-4 focus:ring-sky-200 transition">
                            Khám phá tour quốc tế
                        </a>

                        <a href="#"
                            class="border border-gray-300 bg-white px-6 py-3 rounded-lg font-semibold text-gray-800 hover:bg-gray-50 focus:ring-4 focus:ring-gray-200">
                            Tư vấn miễn phí
                        </a>
                    </div>

                    <!-- STATS -->
                    <div class="mt-8 grid grid-cols-3 gap-4">
                        <div class="bg-white/90 backdrop-blur rounded-xl p-4 border border-gray-200">
                            <div class="text-2xl font-bold text-sky-600">500+</div>
                            <div class="text-sm text-gray-600">Tour hấp dẫn</div>
                        </div>
                        <div class="bg-white/90 backdrop-blur rounded-xl p-4 border border-gray-200">
                            <div class="text-2xl font-bold text-sky-600">5.0★</div>
                            <div class="text-sm text-gray-600">Đánh giá</div>
                        </div>
                        <div class="bg-white/90 backdrop-blur rounded-xl p-4 border border-gray-200">
                            <div class="text-2xl font-bold text-sky-600">24/7</div>
                            <div class="text-sm text-gray-600">Hỗ trợ</div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT -->
                <div class="lg:col-span-6">
                    <div class="bg-white/95 backdrop-blur rounded-2xl shadow-md border border-gray-200 p-6">
                        <h3 class="text-lg font-bold mb-1">Tìm tour nhanh</h3>
                        <p class="text-sm text-gray-600 mb-4">Chọn điểm đến & ngày khởi hành</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input type="text" placeholder="Điểm đến"
                                class="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                            <select
                                class="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                                <option>Chọn loại tour</option>
                                <option>Tour trong nước</option>
                                <option>Tour quốc tế</option>
                            </select>
                            <input type="date"
                                class="rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                            <button
                                class="rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 focus:ring-4 focus:ring-blue-200">
                                Tìm tour
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- FEATURED TOURS -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-screen-xl mx-auto px-4">
            <div class="flex items-end justify-between mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">Tour nổi bật</h2>
                    <p class="text-gray-600 mt-1">Những hành trình được yêu thích nhất</p>
                </div>
                <a href="#" class="text-sky-600 font-semibold hover:underline">Xem tất cả</a>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @for ($i = 1; $i <= 4; $i++)
                    <!-- TOUR CARD -->
                    <div
                        class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition overflow-hidden">

                        <!-- IMAGE -->
                        <div class="relative">
                            <img src="{{ asset('storage/image/bg.png') }}" alt="Tour Thái Lan"
                                class="w-full h-52 object-cover">

                            <!-- Voucher badge -->
                            <span
                                class="absolute top-3 left-3 bg-red-500 text-white text-sm font-semibold px-3 py-1 rounded">
                                Tặng Voucher 500k
                            </span>
                        </div>

                        <!-- CONTENT -->
                        <div class="p-4">
                            <!-- Title -->
                            <h3 class="font-bold text-[17px] leading-snug text-blue-700 hover:text-blue-800 line-clamp-2">
                                Tour Thái Lan 5N4Đ: HCM – Bangkok – Pattaya – Quần Thể Suanthai – Icon Siam
                            </h3>

                            <!-- Rating -->
                            <div class="flex items-center gap-2 mt-2 text-sm">
                                <span class="bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded">
                                    10.0
                                </span>
                                <span class="text-green-600 font-medium">Tuyệt vời</span>
                                <span class="text-gray-500">| 2 đánh giá</span>
                            </div>

                            <!-- Highlights -->
                            <ul class="mt-3 text-sm text-sky-600 space-y-1">
                                <li>• Trải nghiệm cưỡi voi</li>
                                <li>• Chợ nổi bốn miền</li>
                                <li>• Tặng xôi xoài</li>
                                <li>• Xem show Colosseum</li>
                            </ul>

                            <!-- Price -->
                            <div class="mt-4 text-right">
                                <div class="text-sm text-gray-400 line-through">
                                    7.390.000 đ
                                </div>
                                <div class="text-2xl font-extrabold text-orange-500">
                                    6.290.000 đ
                                </div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </section>


    <!-- POPULAR DESTINATIONS -->
    <section class="py-16 bg-white">
        <div class="max-w-screen-xl mx-auto px-4">

            <!-- Heading -->
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-gray-900">
                    Các điểm du lịch phổ biến
                </h2>
                <p class="text-gray-600 mt-2">
                    Những điểm đến được du khách yêu thích nhất
                </p>
            </div>

            <!-- Grid -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Item -->
                <a href="#" class="group relative rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition">
                    <img src="{{ asset('storage/image/logo.png') }}" alt="Phú Quốc"
                        class="w-full h-64 object-cover group-hover:scale-105 transition duration-300">

                    <!-- Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

                    <!-- Content -->
                    <div class="absolute bottom-4 left-4 right-4 text-white">
                        <h3 class="text-xl font-bold">Phú Quốc</h3>
                        <p class="text-sm text-white/90">Biển xanh – Nghỉ dưỡng</p>
                    </div>
                </a>

                <a href="#" class="group relative rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition">
                    <img src="{{ asset('storage/image/logo.png') }}" alt="Đà Nẵng"
                        class="w-full h-64 object-cover group-hover:scale-105 transition duration-300">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                    <div class="absolute bottom-4 left-4 right-4 text-white">
                        <h3 class="text-xl font-bold">Đà Nẵng</h3>
                        <p class="text-sm text-white/90">Thành phố đáng sống</p>
                    </div>
                </a>

                <a href="#" class="group relative rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition">
                    <img src="{{ asset('storage/image/logo.png') }}" alt="Hà Nội"
                        class="w-full h-64 object-cover group-hover:scale-105 transition duration-300">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                    <div class="absolute bottom-4 left-4 right-4 text-white">
                        <h3 class="text-xl font-bold">Hà Nội</h3>
                        <p class="text-sm text-white/90">Văn hoá – Lịch sử</p>
                    </div>
                </a>

                <a href="#" class="group relative rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition">
                    <img src="{{ asset('storage/image/logo.png') }}" alt="Bangkok"
                        class="w-full h-64 object-cover group-hover:scale-105 transition duration-300">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                    <div class="absolute bottom-4 left-4 right-4 text-white">
                        <h3 class="text-xl font-bold">Bangkok</h3>
                        <p class="text-sm text-white/90">Sôi động – Mua sắm</p>
                    </div>
                </a>

                <a href="#" class="group relative rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition">
                    <img src="{{ asset('storage/image/logo.png') }}" alt="Singapore"
                        class="w-full h-64 object-cover group-hover:scale-105 transition duration-300">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                    <div class="absolute bottom-4 left-4 right-4 text-white">
                        <h3 class="text-xl font-bold">Singapore</h3>
                        <p class="text-sm text-white/90">Hiện đại – Sạch đẹp</p>
                    </div>
                </a>

                <!-- View more -->
                <a href="#"
                    class="flex items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 hover:border-sky-500 transition">
                    <span class="text-sky-600 font-semibold">
                        + Xem thêm điểm đến
                    </span>
                </a>

            </div>
        </div>
    </section>

    <!-- NEWS & BLOG -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-screen-xl mx-auto px-4">

            <!-- Heading -->
            <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-10">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">
                        Tin tức & Cẩm nang du lịch
                    </h2>
                    <p class="text-gray-600 mt-2">
                        Cập nhật xu hướng, kinh nghiệm và mẹo hay cho chuyến đi của bạn
                    </p>
                </div>
                <a href="#" class="mt-4 md:mt-0 text-sky-600 font-semibold hover:underline">
                    Xem tất cả bài viết →
                </a>
            </div>

            <!-- Grid -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Main news -->
                <a href="#"
                    class="group col-span-1 lg:col-span-2 bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition">
                    <div class="relative">
                        <img src="{{ asset('storage/image/logo.png') }}" alt="Tin du lịch"
                            class="w-full h-72 object-cover group-hover:scale-105 transition duration-300">
                        <span
                            class="absolute top-4 left-4 bg-sky-600 text-white text-xs font-semibold px-3 py-1 rounded-full">
                            Tin nổi bật
                        </span>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 group-hover:text-sky-600 transition line-clamp-2">
                            Kinh nghiệm du lịch Thái Lan tự túc tiết kiệm cho người mới
                        </h3>
                        <p class="text-gray-600 mt-3 line-clamp-2">
                            Tổng hợp chi tiết lịch trình, chi phí, lưu ý quan trọng giúp bạn
                            có chuyến đi Thái Lan trọn vẹn và tiết kiệm nhất.
                        </p>

                        <div class="mt-4 flex items-center justify-between text-sm text-gray-500">
                            <span>🗓 20/01/2026</span>
                            <span>👁 1.250 lượt xem</span>
                        </div>
                    </div>
                </a>

                <!-- Side news -->
                <div class="space-y-6">

                    <!-- Item -->
                    <a href="#"
                        class="group flex gap-4 bg-white rounded-xl overflow-hidden border border-gray-200 hover:shadow-md transition">
                        <img src="{{ asset('storage/image/logo.png') }}" class="w-28 h-24 object-cover rounded-lg"
                            alt="">
                        <div class="py-3 pr-3">
                            <h4 class="font-semibold text-gray-900 group-hover:text-sky-600 transition line-clamp-2">
                                10 địa điểm check-in không thể bỏ lỡ tại Phú Quốc
                            </h4>
                            <p class="text-xs text-gray-500 mt-1">18/01/2026</p>
                        </div>
                    </a>

                    <a href="#"
                        class="group flex gap-4 bg-white rounded-xl overflow-hidden border border-gray-200 hover:shadow-md transition">
                        <img src="{{ asset('storage/image/logo.png') }}" class="w-28 h-24 object-cover rounded-lg"
                            alt="">
                        <div class="py-3 pr-3">
                            <h4 class="font-semibold text-gray-900 group-hover:text-sky-600 transition line-clamp-2">
                                Du lịch Singapore cần chuẩn bị những gì?
                            </h4>
                            <p class="text-xs text-gray-500 mt-1">15/01/2026</p>
                        </div>
                    </a>

                    <a href="#"
                        class="group flex gap-4 bg-white rounded-xl overflow-hidden border border-gray-200 hover:shadow-md transition">
                        <img src="{{ asset('storage/image/logo.png') }}" class="w-28 h-24 object-cover rounded-lg"
                            alt="">
                        <div class="py-3 pr-3">
                            <h4 class="font-semibold text-gray-900 group-hover:text-sky-600 transition line-clamp-2">
                                Mẹo săn vé máy bay giá rẻ dịp lễ Tết
                            </h4>
                            <p class="text-xs text-gray-500 mt-1">12/01/2026</p>
                        </div>
                    </a>

                </div>

            </div>
        </div>
    </section>
@endsection
