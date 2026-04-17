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
                        <form class="flex items-center gap-2" method="GET" action="{{ route('tours') }}">
                            <input type="text" name="q" value="{{ request('q') }}"
                                placeholder="Tìm tour: Thái Lan, Phú Quốc, Đà Nẵng..."
                                class="flex-1 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:ring-4 focus:ring-sky-100 focus:border-sky-500">
                            <button type="submit"
                                class="rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-sky-700 focus:ring-4 focus:ring-sky-200">
                                Tìm
                            </button>
                        </form>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span class="text-xs text-gray-500">Gợi ý:</span>
                            <a href="{{ route('tours', ['q' => 'Bangkok']) }}"
                                class="text-xs text-sky-600 hover:underline">Bangkok</a>
                            <a href="{{ route('tours', ['q' => 'Phú Quốc']) }}"
                                class="text-xs text-sky-600 hover:underline">Phú Quốc</a>
                            <a href="{{ route('tours', ['q' => 'Đà Nẵng']) }}"
                                class="text-xs text-sky-600 hover:underline">Đà Nẵng</a>
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
                @php
                    $selectedScopeFilter = !empty($selectedTourScope) && $selectedTourScope !== 'all'
                        ? ($locationFilters['scope_filters'][$selectedTourScope] ?? null)
                        : null;
                    $regionOptions = $selectedScopeFilter
                        ? collect($selectedScopeFilter['regions'] ?? [])->mapWithKeys(fn($region, $regionKey) => [$regionKey => $region['label']])->all()
                        : ($locationFilters['regions'] ?? []);
                    $cityOptions = !empty($selectedRegion) && $selectedRegion !== 'all'
                        ? ($selectedScopeFilter['regions'][$selectedRegion]['cities'] ?? ($locationFilters['cities_by_region'][$selectedRegion] ?? []))
                        : ($selectedScopeFilter['all_cities'] ?? ($locationFilters['all_cities'] ?? []));
                @endphp
                <form method="GET" action="{{ route('tours') }}"
                    class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-lg">Bộ lọc</h3>
                        <a href="{{ route('tours') }}" class="text-sm text-gray-500 hover:text-sky-600 hover:underline">Xoá
                            lọc</a>
                    </div>

                    <!-- Tour scope -->
                    <div class="mb-5">
                        <label class="text-sm font-semibold text-gray-700">Loại tour</label>
                        <select name="tour_scope" id="tour-scope-filter"
                            class="mt-2 w-full rounded-xl border-gray-300 text-sm focus:ring-sky-500 focus:border-sky-500">
                            <option value="all">Tất cả tour</option>
                            @foreach (($locationFilters['tour_scopes'] ?? []) as $scopeValue => $scopeLabel)
                                <option value="{{ $scopeValue }}" {{ $selectedTourScope === $scopeValue ? 'selected' : '' }}>
                                    {{ $scopeLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Region -->
                    <div class="mb-5">
                        <label class="text-sm font-semibold text-gray-700">Vùng miền</label>
                        <select name="region" id="region-filter"
                            class="mt-2 w-full rounded-xl border-gray-300 text-sm focus:ring-sky-500 focus:border-sky-500">
                            <option value="all">Tất cả vùng miền</option>
                            @foreach ($regionOptions as $regionValue => $regionLabel)
                                <option value="{{ $regionValue }}" {{ $selectedRegion === $regionValue ? 'selected' : '' }}>
                                    {{ $regionLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- City -->
                    <div class="mb-5">
                        <label class="text-sm font-semibold text-gray-700">Thành phố</label>
                        <select name="city" id="city-filter"
                            class="mt-2 w-full rounded-xl border-gray-300 text-sm focus:ring-sky-500 focus:border-sky-500">
                            <option value="all">Tất cả thành phố</option>
                            @foreach ($cityOptions as $city)
                                <option value="{{ $city }}" {{ $selectedCity === $city ? 'selected' : '' }}>
                                    {{ $city }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-gray-500">
                            Thứ tự lọc: loại tour → vùng miền → thành phố.
                        </p>
                    </div>

                    <!-- Duration -->
                    <div class="mb-5">
                        <label class="text-sm font-semibold text-gray-700">Số ngày</label>
                        <select name="duration"
                            class="mt-2 w-full rounded-xl border-gray-300 text-sm focus:ring-sky-500 focus:border-sky-500">
                            <option value="">Tất cả</option>
                            <option value="1-3" {{ request('duration') == '1-3' ? 'selected' : '' }}>1 – 3 ngày</option>
                            <option value="4-6" {{ request('duration') == '4-6' ? 'selected' : '' }}>4 – 6 ngày</option>
                            <option value="7plus" {{ request('duration') == '7plus' ? 'selected' : '' }}>Trên 6 ngày
                            </option>
                        </select>
                    </div>

                    <!-- Price range -->
                    <div class="mb-6">
                        <label class="text-sm font-semibold text-gray-700">Khoảng giá</label>
                        <div class="mt-3 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-xs font-medium text-gray-500">Giá tối đa</span>
                                <span id="price-max-display"
                                    class="inline-flex items-center rounded-full bg-white px-3 py-1 text-sm font-semibold text-sky-700 border border-sky-100">
                                    {{ $priceFilter['selected_max'] >= $priceFilter['max'] ? 'Không giới hạn' : number_format($priceFilter['selected_max'], 0, ',', '.') . ' đ' }}
                                </span>
                            </div>

                            <input type="range" name="price_max" id="price-max-range"
                                min="{{ $priceFilter['min'] }}" max="{{ $priceFilter['max'] }}"
                                step="{{ $priceFilter['step'] }}" value="{{ $priceFilter['selected_max'] }}"
                                class="mt-4 w-full accent-sky-600">

                            <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                                <span>{{ number_format($priceFilter['min'], 0, ',', '.') }} đ</span>
                                <span>{{ number_format($priceFilter['max'], 0, ',', '.') }} đ</span>
                            </div>
                        </div>
                    </div>

                    <button
                        class="w-full bg-sky-600 text-white py-2.5 rounded-xl font-semibold hover:bg-sky-700 focus:ring-4 focus:ring-sky-200">
                        Áp dụng lọc
                    </button>

                    <p class="mt-3 text-xs text-gray-500">
                        Mẹo: chọn điểm đến + số ngày để ra kết quả chính xác hơn.
                    </p>
                </form>
            </aside>

            <!-- TOUR LIST -->
            <div class="lg:col-span-3">
                <!-- SORT BAR (đẹp hơn + “chips”) -->
                <div
                    class="bg-white rounded-2xl border border-gray-200 shadow-sm px-5 py-4 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div class="text-sm text-gray-600">
                        @php
                            $from = $tours->firstItem();
                            $to = $tours->lastItem();
                            $total = $tours->total();
                        @endphp
                        Hiển thị
                        <b class="text-gray-900">{{ $from }}–{{ $to }}</b>
                        trên tổng
                        <b class="text-gray-900">{{ $total }}</b>
                        tour
                        @if (request('q'))
                            cho từ khóa
                            <span class="font-semibold text-sky-600">"{{ request('q') }}"</span>
                        @endif
                    </div>

                    <div class="flex items-center gap-3">
                        <form method="GET" action="{{ route('tours') }}" class="flex items-center gap-2">
                            @foreach (request()->except(['sort', 'page']) as $key => $value)
                                @if (is_array($value))
                                    @foreach ($value as $item)
                                        <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                    @endforeach
                                @elseif ($value !== null && $value !== '')
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <select name="sort" onchange="this.form.submit()"
                                class="rounded-xl border-gray-300 text-sm focus:ring-sky-500 focus:border-sky-500">
                                <option value="">Sắp xếp theo</option>
                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>
                                    Giá thấp → cao
                                </option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>
                                    Giá cao → thấp
                                </option>
                                <option value="rating_desc" {{ request('sort') == 'rating_desc' ? 'selected' : '' }}>
                                    Đánh giá cao
                                </option>
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>
                                    Mới nhất
                                </option>
                            </select>
                        </form>
                    </div>
                </div>

                <!-- GRID -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse ($tours as $tour)
                        <div
                            class="group bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-lg transition overflow-hidden">

                            <!-- IMAGE -->
                            <div class="relative overflow-hidden">
                                <img src="{{ $tour->main_image }}" alt="{{ $tour->title }}"
                                    class="w-full h-52 object-cover transition duration-300 group-hover:scale-105">

                                <!-- soft gradient bottom -->
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/25 via-transparent to-transparent">
                                </div>

                                @if ($tour->display_departure)
                                    <span
                                        class="absolute top-3 left-3 bg-sky-600 text-white text-xs font-semibold px-3 py-1 rounded-lg">
                                        🗓 {{ $tour->display_departure }}
                                    </span>
                                @endif

                                <span
                                    class="absolute top-3 right-3 {{ $tour->status_class }} text-white text-xs font-bold px-2 py-1 rounded-lg">
                                    {{ $tour->status_text }}
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
                                    class="font-bold text-[16px] leading-snug text-blue-700 group-hover:text-sky-600 transition line-clamp-2 min-h-[48px]">
                                    {{ $tour->title }}
                                </h3>

                                <!-- meta line -->
                                <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                                    <span class="inline-flex items-center gap-1">🗓 {{ $tour->duration_text }}</span>
                                    <span class="inline-flex items-center gap-1">📍
                                        {{ $tour->destination_display }}</span>
                                </div>

                                <!-- Rating -->
                                <div class="flex items-center gap-2 mt-2 text-sm">
                                    <span class="bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-lg">
                                        {{ $tour->average_rating > 0 ? number_format($tour->average_rating, 1) : '0.0' }}
                                    </span>
                                    <span class="text-green-600 font-medium">{{ $tour->rating_text }}</span>
                                    <span class="text-gray-500">| {{ $tour->reviews_count }} đánh giá</span>
                                </div>

                                <!-- Price -->
                                <div class="mt-4 flex items-end justify-between">

                                    <!-- Giá từ -->
                                    <div class="text-xs text-gray-500">
                                        Giá từ
                                        @if ($tour->display_old_price)
                                            <div class="text-sm text-gray-400 line-through">
                                                {{ number_format($tour->display_old_price, 0, ',', '.') }} đ
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Giá chính -->
                                    <div class="text-2xl font-extrabold text-orange-500">
                                        {{ number_format($tour->display_price, 0, ',', '.') }} đ
                                    </div>

                                </div>

                                <!-- Button -->
                                <a href="{{ route('tours.show', $tour->slug) }}"
                                    class="mt-4 block text-center bg-blue-600 text-white py-2.5 rounded-xl font-semibold hover:bg-blue-700 focus:ring-4 focus:ring-blue-200">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-10 text-gray-500">
                            Hiện chưa có tour nào đang mở bán.
                        </div>
                    @endforelse
                </div>

                <!-- PAGINATION -->
                <div class="mt-10 flex justify-center">
                    {{ $tours->links('pagination::tailwind') }}
                </div>

            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const scopeSelect = document.getElementById('tour-scope-filter');
            const regionSelect = document.getElementById('region-filter');
            const citySelect = document.getElementById('city-filter');
            const priceMaxRange = document.getElementById('price-max-range');
            const priceMaxDisplay = document.getElementById('price-max-display');
            const scopeFilters = @json($locationFilters['scope_filters'] ?? []);
            const regionLabels = @json($locationFilters['regions'] ?? []);
            const citiesByRegion = @json($locationFilters['cities_by_region'] ?? []);
            const allCities = @json($locationFilters['all_cities'] ?? []);

            if (!scopeSelect || !regionSelect || !citySelect) {
                return;
            }

            const buildOption = function(value, label, selectedValue) {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = label;

                if (value === selectedValue) {
                    option.selected = true;
                }

                return option;
            };

            const resolveRegionOptions = function(scopeValue) {
                if (scopeValue && scopeValue !== 'all' && scopeFilters[scopeValue]) {
                    return Object.entries(scopeFilters[scopeValue].regions || {}).map(function(entry) {
                        return {
                            value: entry[0],
                            label: entry[1].label
                        };
                    });
                }

                return Object.entries(regionLabels).map(function(entry) {
                    return {
                        value: entry[0],
                        label: entry[1]
                    };
                });
            };

            const resolveCityOptions = function(scopeValue, regionValue) {
                if (scopeValue && scopeValue !== 'all' && scopeFilters[scopeValue]) {
                    if (regionValue && regionValue !== 'all') {
                        return scopeFilters[scopeValue].regions?.[regionValue]?.cities || [];
                    }

                    return scopeFilters[scopeValue].all_cities || [];
                }

                if (regionValue && regionValue !== 'all') {
                    return citiesByRegion[regionValue] || [];
                }

                return allCities;
            };

            const renderRegionOptions = function(scopeValue, selectedValue) {
                const nextRegions = resolveRegionOptions(scopeValue);
                const validRegionValues = nextRegions.map(function(region) {
                    return region.value;
                });
                const resolvedSelectedValue = validRegionValues.includes(selectedValue) ? selectedValue : 'all';

                regionSelect.innerHTML = '';
                regionSelect.appendChild(buildOption('all', 'Tất cả vùng miền', resolvedSelectedValue));

                nextRegions.forEach(function(region) {
                    regionSelect.appendChild(buildOption(region.value, region.label, resolvedSelectedValue));
                });

                return resolvedSelectedValue;
            };

            const renderCityOptions = function(scopeValue, regionValue, selectedValue) {
                const nextCities = resolveCityOptions(scopeValue, regionValue);
                const isSelectedCityValid = nextCities.includes(selectedValue);

                citySelect.innerHTML = '';
                citySelect.appendChild(buildOption('all', 'Tất cả thành phố', 'all'));

                nextCities.forEach(function(city) {
                    citySelect.appendChild(buildOption(city, city, isSelectedCityValid ? selectedValue : 'all'));
                });
            };

            const syncDependentFilters = function() {
                const resolvedRegionValue = renderRegionOptions(scopeSelect.value, regionSelect.value || 'all');
                renderCityOptions(scopeSelect.value, resolvedRegionValue, citySelect.value || 'all');
            };

            syncDependentFilters();

            scopeSelect.addEventListener('change', function() {
                syncDependentFilters();
            });

            regionSelect.addEventListener('change', function() {
                renderCityOptions(scopeSelect.value, regionSelect.value, citySelect.value || 'all');
            });

            if (priceMaxRange && priceMaxDisplay) {
                const formatter = new Intl.NumberFormat('vi-VN');

                const updatePriceDisplay = function() {
                    const selectedValue = Number(priceMaxRange.value || 0);
                    const maxValue = Number(priceMaxRange.max || 0);

                    priceMaxDisplay.textContent = selectedValue >= maxValue
                        ? 'Không giới hạn'
                        : formatter.format(selectedValue) + ' đ';
                };

                updatePriceDisplay();
                priceMaxRange.addEventListener('input', updatePriceDisplay);
                priceMaxRange.addEventListener('change', updatePriceDisplay);
            }
        });
    </script>
@endsection
