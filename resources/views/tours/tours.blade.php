@php
    $title = 'Danh sách tour';
@endphp
@extends('layouts.app-guest')

@section('content')
    <!-- PAGE HEADER (nền nhẹ + breadcrumb + search) -->
    <section class="pt-28 pb-10 bg-gradient-to-b from-sky-50 to-white border-b border-gray-200">
        <div class="max-w-screen-xl mx-auto px-4">
            <!-- breadcrumb -->
            <nav class="text-sm text-gray-500 mb-3">
                <ol class="flex items-center gap-2">
                    <li><a href="{{ route('home') }}" class="hover:text-sky-600">Trang chủ</a></li>
                    <li class="text-gray-400">/</li>
                    <li class="text-gray-700 font-medium">Danh sách tour</li>
                </ol>
            </nav>

            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5">
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900">Danh sách tour</h1>
                    <p class="text-gray-600 mt-1">
                        Khám phá tour du lịch trong nước & quốc tế, ưu đãi mỗi ngày
                    </p>
                </div>

                <!-- quick search -->
                <div class="w-full lg:w-[460px]">
                    <div class="bg-white/90 backdrop-blur border border-gray-200 rounded-2xl shadow-sm p-3">
                        <div class="flex items-center gap-2">
                            <input type="text" placeholder="Tìm tour: Thái Lan, Phú Quốc, Đà Nẵng..."
                                class="flex-1 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:ring-4 focus:ring-sky-100 focus:border-sky-500">
                            <button
                                class="rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-sky-700 focus:ring-4 focus:ring-sky-200">
                                Tìm
                            </button>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span class="text-xs text-gray-500">Gợi ý:</span>
                            <a href="#" class="text-xs text-sky-600 hover:underline">Bangkok</a>
                            <a href="#" class="text-xs text-sky-600 hover:underline">Phú Quốc</a>
                            <a href="#" class="text-xs text-sky-600 hover:underline">Đà Nẵng</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTENT -->
    <section class="py-10">
        <div class="max-w-screen-xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-4 gap-8">

            <!-- FILTER (đẹp hơn + xoá lọc + range giá) -->
            <aside class="lg:col-span-1 sticky top-28 h-fit">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-lg">Bộ lọc</h3>
                        <a href="#" class="text-sm text-gray-500 hover:text-sky-600 hover:underline">Xoá lọc</a>
                    </div>

                    <!-- Destination -->
                    <div class="mb-5">
                        <label class="text-sm font-semibold text-gray-700">Điểm đến</label>
                        <select
                            class="mt-2 w-full rounded-xl border-gray-300 text-sm focus:ring-sky-500 focus:border-sky-500">
                            <option>Tất cả</option>
                            <option>Phú Quốc</option>
                            <option>Đà Nẵng</option>
                            <option>Bangkok</option>
                            <option>Singapore</option>
                        </select>
                    </div>

                    <!-- Tour type -->
                    <div class="mb-5">
                        <label class="text-sm font-semibold text-gray-700">Loại tour</label>
                        <div class="mt-2 space-y-2 text-sm">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" class="rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                                <span>Tour trong nước</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" class="rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                                <span>Tour quốc tế</span>
                            </label>
                        </div>
                    </div>

                    <!-- Duration -->
                    <div class="mb-5">
                        <label class="text-sm font-semibold text-gray-700">Số ngày</label>
                        <select
                            class="mt-2 w-full rounded-xl border-gray-300 text-sm focus:ring-sky-500 focus:border-sky-500">
                            <option>Tất cả</option>
                            <option>1 – 3 ngày</option>
                            <option>4 – 6 ngày</option>
                            <option>Trên 6 ngày</option>
                        </select>
                    </div>

                    <!-- Price range (UI đẹp, chưa cần JS) -->
                    <div class="mb-6">
                        <label class="text-sm font-semibold text-gray-700">Khoảng giá</label>
                        <div class="mt-3">
                            <input type="range" min="0" max="100" value="40"
                                class="w-full accent-sky-600">
                            <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                                <span>0đ</span>
                                <span>~ 10.000.000đ</span>
                                <span>20.000.000đ</span>
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-3">
                            <input type="text" placeholder="Từ (đ)"
                                class="rounded-xl border border-gray-200 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-sky-100 focus:border-sky-500">
                            <input type="text" placeholder="Đến (đ)"
                                class="rounded-xl border border-gray-200 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-sky-100 focus:border-sky-500">
                        </div>
                    </div>

                    <button
                        class="w-full bg-sky-600 text-white py-2.5 rounded-xl font-semibold hover:bg-sky-700 focus:ring-4 focus:ring-sky-200">
                        Áp dụng lọc
                    </button>

                    <p class="mt-3 text-xs text-gray-500">
                        Mẹo: chọn điểm đến + số ngày để ra kết quả chính xác hơn.
                    </p>
                </div>
            </aside>

            <!-- TOUR LIST -->
            <div class="lg:col-span-3">
                <!-- SORT BAR (đẹp hơn + “chips”) -->
                <div
                    class="bg-white rounded-2xl border border-gray-200 shadow-sm px-5 py-4 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div class="text-sm text-gray-600">
                        Hiển thị <b class="text-gray-900">12</b> tour
                    </div>

                    <div class="flex items-center gap-3">
                        <select class="rounded-xl border-gray-300 text-sm focus:ring-sky-500 focus:border-sky-500">
                            <option>Sắp xếp theo</option>
                            <option>Giá thấp → cao</option>
                            <option>Giá cao → thấp</option>
                            <option>Đánh giá cao</option>
                            <option>Mới nhất</option>
                        </select>
                    </div>
                </div>

                <!-- GRID -->
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">

                    @for ($i = 1; $i <= 9; $i++)
                        <div
                            class="group bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-lg transition overflow-hidden">

                            <!-- IMAGE -->
                            <div class="relative overflow-hidden">
                                <img src="{{ asset('storage/image/bg.png') }}" alt="Tour"
                                    class="w-full h-52 object-cover transition duration-300 group-hover:scale-105">

                                <!-- soft gradient bottom -->
                                <div class="absolute inset-0 bg-gradient-to-t from-black/25 via-transparent to-transparent">
                                </div>

                                @php
                                    $departures = ['🗓 19/02', '🗓 22/02', '🗓 01/03'];
                                    $statuses = [
                                        ['text' => '✔ Còn chỗ', 'class' => 'bg-emerald-600'],
                                        ['text' => '⏳ Sắp hết chỗ', 'class' => 'bg-amber-500'],
                                        ['text' => '🔥 Nhiều người quan tâm', 'class' => 'bg-pink-600'],
                                    ];

                                    $departure = $departures[array_rand($departures)];
                                    $status = $statuses[array_rand($statuses)];
                                @endphp

                                <span
                                    class="absolute top-3 left-3 bg-sky-600 text-white text-xs font-semibold px-3 py-1 rounded-lg">
                                    {{ $departure }}
                                </span>

                                <span
                                    class="absolute top-3 right-3 {{ $status['class'] }} text-white text-xs font-bold px-2 py-1 rounded-lg">
                                    {{ $status['text'] }}
                                </span>


                                <!-- quick action (UI) -->
                                <div
                                    class="absolute bottom-3 right-3 flex gap-2 opacity-0 group-hover:opacity-100 transition">
                                    <button
                                        class="w-9 h-9 rounded-full bg-white/90 backdrop-blur border border-gray-200 hover:bg-white">
                                        ❤
                                    </button>
                                    <button
                                        class="w-9 h-9 rounded-full bg-white/90 backdrop-blur border border-gray-200 hover:bg-white">
                                        ⇪
                                    </button>
                                </div>
                            </div>

                            <!-- CONTENT -->
                            <div class="p-4">
                                <!-- Title -->
                                <h3
                                    class="font-bold text-[16px] leading-snug text-blue-700 group-hover:text-sky-600 transition line-clamp-2">
                                    Tour Thái Lan 5N4Đ: HCM – Bangkok – Pattaya – Icon Siam
                                </h3>

                                <!-- meta line -->
                                <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                                    <span class="inline-flex items-center gap-1">🗓 5N4Đ</span>
                                    <span class="inline-flex items-center gap-1">📍 Bangkok</span>
                                </div>

                                <!-- Rating -->
                                <div class="flex items-center gap-2 mt-2 text-sm">
                                    <span class="bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-lg">
                                        5.0
                                    </span>
                                    <span class="text-green-600 font-medium">Tuyệt vời</span>
                                    <span class="text-gray-500">| 2 đánh giá</span>
                                </div>

                                <!-- Price -->
                                <div class="mt-4 flex items-end justify-between">
                                    <div class="text-xs text-gray-500">
                                        Giá từ
                                        <div class="text-sm text-gray-400 line-through">7.390.000 đ</div>
                                    </div>
                                    <div class="text-2xl font-extrabold text-orange-500">
                                        6.290.000 đ
                                    </div>
                                </div>

                                <!-- Button -->
                                <a href="{{ route('tours.show', 'slug-example') }}"
                                    class="mt-4 block text-center bg-blue-600 text-white py-2.5 rounded-xl font-semibold hover:bg-blue-700 focus:ring-4 focus:ring-blue-200">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    @endfor

                </div>

                <!-- PAGINATION -->
                <div class="mt-10 flex justify-center">
                    <nav class="flex items-center gap-2">
                        <a href="{{ route('tours.show', 'slug-example') }}"
                            class="px-3 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50">«</a>

                        <a href="{{ route('tours.show', 'slug-example') }}"
                            class="px-4 py-2 rounded-xl bg-sky-600 text-white font-semibold">1</a>
                        <a href="{{ route('tours.show', 'slug-example') }}"
                            class="px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50">2</a>
                        <a href="{{ route('tours.show', 'slug-example') }}"
                            class="px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50">3</a>

                        <a href="{{ route('tours.show', 'slug-example') }}"
                            class="px-3 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50">»</a>
                    </nav>
                </div>

            </div>
        </div>
    </section>
@endsection
