@php
    $title = 'Vie Travel - Sửa thông tin đặt tour';
    $order = $order ?? $booking->order;
    $departure = $booking->departure;
    $tour = $departure ? $departure->tour : null;
    $passengers = $booking->passengers;
@endphp

@extends('layouts.app-guest')

@section('content')
    <section class="pt-28 pb-16 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-xs font-semibold text-sky-600 uppercase tracking-wide mb-1">Sửa thông tin đặt tour</p>
                    <h1 class="text-2xl font-bold text-gray-900">
                        #{{ $order->order_code ?? 'BK' . $booking->id }}
                    </h1>
                    <p class="text-xs text-gray-500 mt-1">
                        Bạn có thể chỉnh sửa thông tin liên hệ và hành khách trước 7 ngày khởi hành. Đơn đã thanh toán đủ sẽ
                        không được sửa.
                    </p>
                </div>
                <div class="text-right space-y-2">
                    <a href="{{ route('dashboard.bookings.show', $booking->id) }}"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50">
                        Xem hóa đơn
                    </a>
                    <div>
                        <a href="{{ route('dashboard') }}"
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50">
                            Quay lại dashboard
                        </a>
                    </div>
                </div>
            </div>

            <form action="{{ route('dashboard.bookings.update', $booking->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <h2 class="text-sm font-semibold text-gray-900 mb-3">Thông tin tour</h2>
                        @if ($tour)
                            <p class="text-sm font-semibold text-gray-900 mb-1">{{ $tour->title }}</p>
                            <p class="text-xs text-gray-500 mb-3">{{ $tour->destination_text }}</p>
                            <dl class="space-y-1 text-xs text-gray-600">
                                <div class="flex justify-between">
                                    <dt>Mã tour</dt>
                                    <dd class="font-medium text-gray-900">{{ $tour->code }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt>Điểm khởi hành</dt>
                                    <dd class="font-medium text-gray-900">{{ $tour->departure_location }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt>Thời gian</dt>
                                    <dd class="font-medium text-gray-900">
                                        {{ $tour->duration_days }}N{{ $tour->duration_nights }}Đ</dd>
                                </div>
                            </dl>
                        @else
                            <p class="text-xs text-gray-500">Tour đã bị ẩn hoặc không còn khả dụng.</p>
                        @endif
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <h2 class="text-sm font-semibold text-gray-900 mb-3">Thông tin lịch khởi hành</h2>
                        @if ($departure)
                            <dl class="space-y-1 text-xs text-gray-600">
                                <div class="flex justify-between">
                                    <dt>Ngày khởi hành</dt>
                                    <dd class="font-medium text-gray-900">
                                        {{ \Carbon\Carbon::parse($departure->start_date)->format('d/m/Y') }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt>Ngày kết thúc</dt>
                                    <dd class="font-medium text-gray-900">
                                        {{ \Carbon\Carbon::parse($departure->end_date)->format('d/m/Y') }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt>Điểm tập trung</dt>
                                    <dd class="font-medium text-gray-900">{{ $departure->meeting_point }}</dd>
                                </div>
                            </dl>
                        @else
                            <p class="text-xs text-gray-500">Không tìm thấy thông tin lịch khởi hành.</p>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Thông tin liên hệ</h2>
                    <div class="grid gap-4 md:grid-cols-2 text-sm">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Tên liên hệ *</label>
                            <input type="text" name="contact_name"
                                value="{{ old('contact_name', $order->contact_name) }}"
                                class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                            @error('contact_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Số điện thoại *</label>
                            <input type="text" name="contact_phone"
                                value="{{ old('contact_phone', $order->contact_phone) }}"
                                class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                            @error('contact_phone')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Email *</label>
                            <input type="email" name="contact_email"
                                value="{{ old('contact_email', $order->contact_email) }}"
                                class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                            @error('contact_email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Ghi chú</label>
                            <textarea name="note" rows="3"
                                class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">{{ old('note', $booking->note) }}</textarea>
                            @error('note')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-4 items-start">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex-1 w-full">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-sm font-semibold text-gray-900">Danh sách hành khách</h2>
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <span>Thêm khách:</span>
                                <button type="button" data-add-passenger="adult"
                                    class="px-2 py-1 rounded-lg border border-gray-200 bg-white hover:bg-gray-50">
                                    Người lớn
                                </button>
                                <button type="button" data-add-passenger="child"
                                    class="px-2 py-1 rounded-lg border border-gray-200 bg-white hover:bg-gray-50">
                                    Trẻ em
                                </button>
                                <button type="button" data-add-passenger="infant"
                                    class="px-2 py-1 rounded-lg border border-gray-200 bg-white hover:bg-gray-50">
                                    Trẻ nhỏ
                                </button>
                                <button type="button" data-add-passenger="youth"
                                    class="px-2 py-1 rounded-lg border border-gray-200 bg-white hover:bg-gray-50">
                                    Em bé
                                </button>
                            </div>
                        </div>

                        @error('passengers')
                            <p class="mb-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        <div id="passengerContainer" class="space-y-3 text-xs">
                            @foreach ($passengers as $index => $p)
                                @php
                                    $currentType = old('passengers.' . $index . '.passenger_type', $p->passenger_type);
                                @endphp
                                <div class="border border-gray-200 rounded-xl p-3" data-passenger-row>
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="font-semibold text-gray-900">Hành khách <span
                                                data-passenger-index>{{ $index + 1 }}</span></p>
                                        <div class="flex items-center gap-2">
                                            <select name="passengers[{{ $index }}][passenger_type]"
                                                class="rounded-lg border border-gray-200 px-2 py-1 text-[11px] bg-white focus:ring-2 focus:ring-sky-100 focus:border-sky-500">
                                                <option value="adult"
                                                    {{ old('passengers.' . $index . '.passenger_type', $p->passenger_type) === 'adult' ? 'selected' : '' }}>
                                                    Người lớn</option>
                                                <option value="child"
                                                    {{ old('passengers.' . $index . '.passenger_type', $p->passenger_type) === 'child' ? 'selected' : '' }}>
                                                    Trẻ em</option>
                                                <option value="infant"
                                                    {{ old('passengers.' . $index . '.passenger_type', $p->passenger_type) === 'infant' ? 'selected' : '' }}>
                                                    Trẻ nhỏ</option>
                                                <option value="youth"
                                                    {{ old('passengers.' . $index . '.passenger_type', $p->passenger_type) === 'youth' ? 'selected' : '' }}>
                                                    Em bé</option>
                                            </select>
                                            <button type="button" data-remove-passenger
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg border border-rose-200 bg-rose-50 text-[11px] font-medium text-rose-700 hover:bg-rose-100">
                                                Xóa
                                            </button>
                                        </div>
                                    </div>
                                    <input type="hidden" name="passengers[{{ $index }}][id]"
                                        value="{{ $p->id }}">
                                    <div class="grid gap-3 md:grid-cols-3">
                                        <div>
                                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Họ tên *</label>
                                            <input type="text" name="passengers[{{ $index }}][full_name]"
                                                value="{{ old('passengers.' . $index . '.full_name', $p->full_name) }}"
                                                class="w-full rounded-xl border border-gray-200 px-3 py-2 text-xs focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Giới
                                                tính</label>
                                            <select name="passengers[{{ $index }}][gender]"
                                                class="w-full rounded-xl border border-gray-200 px-3 py-2 text-xs bg-white focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                                                <option value="male"
                                                    {{ old('passengers.' . $index . '.gender', $p->gender) === 'male' ? 'selected' : '' }}>
                                                    Nam</option>
                                                <option value="female"
                                                    {{ old('passengers.' . $index . '.gender', $p->gender) === 'female' ? 'selected' : '' }}>
                                                    Nữ</option>
                                                <option value="other"
                                                    {{ old('passengers.' . $index . '.gender', $p->gender) === 'other' ? 'selected' : '' }}>
                                                    Khác</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Ngày
                                                sinh</label>
                                            <input type="date" name="passengers[{{ $index }}][dob]"
                                                value="{{ old('passengers.' . $index . '.dob', $p->dob ? \Carbon\Carbon::parse($p->dob)->format('Y-m-d') : '') }}"
                                                class="w-full rounded-xl border border-gray-200 px-3 py-2 text-xs focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                                        </div>
                                    </div>
                                    <div class="mt-2 {{ $currentType === 'adult' ? '' : 'hidden' }}"
                                        data-single-room-wrapper>
                                        <label class="inline-flex items-center gap-2 text-[11px] text-gray-700">
                                            <input type="checkbox" name="passengers[{{ $index }}][single_room]"
                                                value="1"
                                                class="rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                                                {{ old('passengers.' . $index . '.single_room', $p->single_room) ? 'checked' : '' }}>
                                            <span>Phòng đơn</span>
                                        </label>
                                        @if(!empty($departure->single_room_surcharge) && $departure->single_room_surcharge > 0)
                                            <p class="mt-1 pl-6 text-[11px] text-gray-500">
                                                Phụ thu: {{ number_format($departure->single_room_surcharge, 0, ',', '.') }} đ/khách
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mt-4 lg:mt-0 lg:w-80 lg:sticky lg:top-24">
                        <h2 class="text-sm font-semibold text-gray-900 mb-3">Thông tin giá (ước tính)</h2>

                        <div class="space-y-1 text-xs mb-3">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Người lớn</span>
                                <span id="editAdultBreakdown" class="font-semibold text-gray-900">0 x 0 đ</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Trẻ em</span>
                                <span id="editChildBreakdown" class="font-semibold text-gray-900">0 x 0 đ</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Trẻ nhỏ</span>
                                <span id="editInfantBreakdown" class="font-semibold text-gray-900">0 x 0 đ</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Em bé</span>
                                <span id="editYouthBreakdown" class="font-semibold text-gray-900">0 x 0 đ</span>
                            </div>
                            <div
                                class="flex items-center justify-between pt-1 border-t border-dashed border-gray-200 mt-1">
                                <span class="text-gray-600">Phụ thu phòng đơn</span>
                                <span id="editSingleBreakdown" class="font-semibold text-gray-900">0 đ</span>
                            </div>
                        </div>

                        <div class="mt-3 rounded-xl border border-gray-100 bg-gray-50 p-3 text-xs space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Tổng tiền mới (ước tính)</span>
                                <span id="editTotalAmount" class="font-semibold text-gray-900">0 đ</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Đã cọc / đã thanh toán</span>
                                <span id="editPaidAmount"
                                    class="font-semibold text-emerald-700">{{ number_format($paidAmount ?? 0, 0, ',', '.') }}
                                    đ</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Còn lại dự kiến</span>
                                <span id="editRemainingAmount" class="font-semibold text-amber-700">0 đ</span>
                            </div>
                        </div>

                        <p class="mt-2 text-[11px] text-gray-500">Giá hiển thị chỉ mang tính tham khảo trước khi lưu. Sau
                            khi lưu, hệ thống sẽ cập nhật lại tổng tiền và số tiền cần thanh toán tiếp theo.</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('dashboard.bookings.show', $booking->id) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50">
                        Hủy
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 focus:ring-4 focus:ring-blue-200">
                        Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </section>
    <script>
        (function() {
            const container = document.getElementById('passengerContainer');
            if (!container) return;

            let index = {{ $passengers->count() }};

            const priceAdult = {{ (int) ($departure->price_adult ?? 0) }};
            const priceChild = {{ (int) ($departure->price_child ?? 0) }};
            const priceInfant = {{ (int) ($departure->price_infant ?? 0) }};
            const priceYouth = {{ (int) ($departure->price_youth ?? 0) }};
            const singleRoomPrice = {{ (int) ($departure->single_room_surcharge ?? 0) }};
            const paidAmount = {{ (int) ($paidAmount ?? 0) }};

            const elAdult = document.getElementById('editAdultBreakdown');
            const elChild = document.getElementById('editChildBreakdown');
            const elInfant = document.getElementById('editInfantBreakdown');
            const elYouth = document.getElementById('editYouthBreakdown');
            const elSingle = document.getElementById('editSingleBreakdown');
            const elTotal = document.getElementById('editTotalAmount');
            const elPaid = document.getElementById('editPaidAmount');
            const elRemaining = document.getElementById('editRemainingAmount');

            function formatVND(number) {
                try {
                    return new Intl.NumberFormat('vi-VN').format(number) + ' đ';
                } catch (e) {
                    return number + ' đ';
                }
            }

            function updateIndexes() {
                const rows = container.querySelectorAll('[data-passenger-row]');
                rows.forEach((row, i) => {
                    const label = row.querySelector('[data-passenger-index]');
                    if (label) label.textContent = i + 1;

                    row.querySelectorAll('input[name], select[name]').forEach((el) => {
                        const name = el.getAttribute('name');
                        if (!name) return;
                        const newName = name.replace(/passengers\[[0-9]+\]/, 'passengers[' + i + ']');
                        el.setAttribute('name', newName);
                    });
                });
            }

            function updateSingleRoomVisibility(row) {
                if (!row) return;
                const typeSelect = row.querySelector('select[name*="[passenger_type]"]');
                const wrapper = row.querySelector('[data-single-room-wrapper]');
                if (!typeSelect || !wrapper) return;

                const type = typeSelect.value;
                const checkbox = wrapper.querySelector('input[type="checkbox"]');

                if (type === 'adult') {
                    wrapper.classList.remove('hidden');
                } else {
                    wrapper.classList.add('hidden');
                    if (checkbox) {
                        checkbox.checked = false;
                    }
                }
            }

            function createRow(type) {
                const row = document.createElement('div');
                row.className = 'border border-gray-200 rounded-xl p-3';
                row.setAttribute('data-passenger-row', '');

                row.innerHTML = `
                    <div class="flex items-center justify-between mb-2">
                        <p class="font-semibold text-gray-900">Hành khách <span data-passenger-index></span></p>
                        <div class="flex items-center gap-2">
                            <select name="passengers[${index}][passenger_type]"
                                class="rounded-lg border border-gray-200 px-2 py-1 text-[11px] bg-white focus:ring-2 focus:ring-sky-100 focus:border-sky-500">
                                <option value="adult" ${type === 'adult' ? 'selected' : ''}>Người lớn</option>
                                <option value="child" ${type === 'child' ? 'selected' : ''}>Trẻ em</option>
                                <option value="infant" ${type === 'infant' ? 'selected' : ''}>Trẻ nhỏ</option>
                                <option value="youth" ${type === 'youth' ? 'selected' : ''}>Em bé</option>
                            </select>
                            <button type="button" data-remove-passenger
                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg border border-rose-200 bg-rose-50 text-[11px] font-medium text-rose-700 hover:bg-rose-100">
                                Xóa
                            </button>
                        </div>
                    </div>
                    <div class="grid gap-3 md:grid-cols-3">
                        <div>
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Họ tên *</label>
                            <input type="text" name="passengers[${index}][full_name]"
                                class="w-full rounded-xl border border-gray-200 px-3 py-2 text-xs focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Giới tính</label>
                            <select name="passengers[${index}][gender]"
                                class="w-full rounded-xl border border-gray-200 px-3 py-2 text-xs bg-white focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                                <option value="male">Nam</option>
                                <option value="female">Nữ</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-gray-600 mb-1">Ngày sinh</label>
                            <input type="date" name="passengers[${index}][dob]"
                                class="w-full rounded-xl border border-gray-200 px-3 py-2 text-xs focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                        </div>
                    </div>
                    <div class="mt-2 ${type === 'adult' ? '' : 'hidden'}" data-single-room-wrapper>
                        <label class="inline-flex items-center gap-2 text-[11px] text-gray-700">
                            <input type="checkbox" name="passengers[${index}][single_room]" value="1"
                                class="rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                            <span>Phòng đơn (có phụ thu nếu áp dụng)</span>
                        </label>
                    </div>
                `;

                container.appendChild(row);
                index++;
                updateIndexes();
                updateSingleRoomVisibility(row);
                recalcPrice();
            }

            // Gán lại index ban đầu
            updateIndexes();
            container.querySelectorAll('[data-passenger-row]').forEach(updateSingleRoomVisibility);
            recalcPrice();

            // Xử lý nút thêm khách
            document.querySelectorAll('[data-add-passenger]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const type = btn.getAttribute('data-add-passenger') || 'adult';
                    createRow(type);
                });
            });

            // Xử lý xóa khách (event delegation)
            container.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-remove-passenger]');
                if (!btn) return;
                const row = btn.closest('[data-passenger-row]');
                if (row) {
                    row.remove();
                    updateIndexes();
                    recalcPrice();
                }
            });

            // Ẩn/hiện checkbox phòng đơn khi đổi loại khách
            container.addEventListener('change', (e) => {
                const select = e.target.closest('select[name*="[passenger_type]"]');
                const row = e.target.closest('[data-passenger-row]');
                if (select && row) {
                    updateSingleRoomVisibility(row);
                }
                recalcPrice();
            });

            function recalcPrice() {
                if (!elTotal || !container) return;

                let adultCount = 0;
                let childCount = 0;
                let infantCount = 0;
                let youthCount = 0;
                let singleCount = 0;

                const rows = container.querySelectorAll('[data-passenger-row]');
                rows.forEach((row) => {
                    const typeSelect = row.querySelector('select[name*="[passenger_type]"]');
                    const type = typeSelect ? typeSelect.value : 'adult';
                    const singleWrapper = row.querySelector('[data-single-room-wrapper]');
                    const singleCheckbox = singleWrapper ? singleWrapper.querySelector(
                        'input[type="checkbox"]') : null;
                    const hasSingle = singleWrapper && !singleWrapper.classList.contains('hidden') &&
                        singleCheckbox && singleCheckbox.checked;

                    switch (type) {
                        case 'child':
                            childCount++;
                            break;
                        case 'infant':
                            infantCount++;
                            break;
                        case 'youth':
                            youthCount++;
                            break;
                        default:
                            adultCount++;
                            if (hasSingle) singleCount++;
                            break;
                    }
                });

                const adultTotal = adultCount * priceAdult;
                const childTotal = childCount * priceChild;
                const infantTotal = infantCount * priceInfant;
                const youthTotal = youthCount * priceYouth;
                const singleTotal = singleCount * singleRoomPrice;
                const total = adultTotal + childTotal + infantTotal + youthTotal + singleTotal;

                if (elAdult) elAdult.textContent = adultCount + ' x ' + formatVND(priceAdult);
                if (elChild) elChild.textContent = childCount + ' x ' + formatVND(priceChild);
                if (elInfant) elInfant.textContent = infantCount + ' x ' + formatVND(priceInfant);
                if (elYouth) elYouth.textContent = youthCount + ' x ' + formatVND(priceYouth);
                if (elSingle) elSingle.textContent = formatVND(singleTotal);

                if (elTotal) elTotal.textContent = formatVND(total);
                if (elPaid) elPaid.textContent = formatVND(paidAmount);
                if (elRemaining) {
                    const remaining = Math.max(total - paidAmount, 0);
                    elRemaining.textContent = formatVND(remaining);
                }
            }
        })();
    </script>
@endsection
