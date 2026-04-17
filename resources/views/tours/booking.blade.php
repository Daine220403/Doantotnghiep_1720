@php
    $title = 'Vie Travel - Đặt tour';
    use Carbon\Carbon;
@endphp

@extends('layouts.app-guest')

@section('content')
    <section class="pt-28 pb-6 bg-white border-b border-gray-200">
        <div class="max-w-screen-xl mx-auto px-4">
            <nav class="text-sm text-gray-500 mb-3">
                <ol class="flex flex-wrap items-center gap-2">
                    <li><a href="{{ route('home') }}" class="hover:text-sky-600">Trang chủ</a></li>
                    <li class="opacity-60">/</li>
                    <li><a href="{{ route('tours') }}" class="hover:text-sky-600">Tours</a></li>
                    <li class="opacity-60">/</li>
                    <li><a href="{{ route('tours.show', $tour->slug) }}" class="hover:text-sky-600">{{ $tour->title }}</a></li>
                    <li class="opacity-60">/</li>
                    <li class="text-gray-900 font-medium">Đặt tour</li>
                </ol>
            </nav>

            <div class="flex flex-col gap-2">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
                    {{ $tour->title }}
                </h1>

                <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600">
                    <span class="inline-flex items-center gap-2">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Ngày khởi hành:</span>
                        <b class="text-red-600">
                            {{ Carbon::parse($departure->start_date)->format('d/m/Y') }}
                        </b>
                    </span>

                    <span class="hidden md:inline text-gray-300">•</span>

                    <span class="inline-flex items-center gap-2">
                        <i class="fas fa-clock"></i>
                        {{ $tour->duration_days }} ngày {{ $tour->duration_nights }} đêm
                    </span>

                    <span class="hidden md:inline text-gray-300">•</span>

                    <span class="inline-flex items-center gap-2">
                        <i class="fas fa-map-marker-alt"></i>
                        Điểm tập trung: <b>{{ $departure->meeting_point ?? $tour->departure_location }}</b>
                    </span>
                </div>
            </div>
        </div>
    </section>

    <section class="py-8 bg-gray-50">
        <form action="{{ route('tours.checkout') }}" method="POST" class="space-y-5" id="bookingForm">
            @csrf
            <input type="hidden" name="tour_id" value="{{ $tour->id }}">
            <input type="hidden" name="schedule_id" value="{{ $departure->id }}">

            <div class="max-w-screen-xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- LEFT: FORM FIELDS -->
                <div class="lg:col-span-8 space-y-6">
                    <div class="bg-white rounded-2xl border border-gray-200 p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-bold text-gray-900">Thông tin đặt tour</h2>
                            <a href="{{ route('tours.show', $tour->slug) }}" class="text-sm text-sky-600 hover:text-sky-700">
                                Chọn ngày khác
                            </a>
                        </div>

                        <!-- SỐ LƯỢNG KHÁCH -->
                        <div>
                            <h3 class="text-md font-semibold text-gray-900 mb-3">Số lượng khách</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div>
                                    <label class="text-sm font-semibold text-gray-700">Người lớn</label>
                                    <input id="adultQty" type="number" name="adults" min="1" value="1"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-gray-700">Trẻ em (5–11 tuổi)</label>
                                    <input id="childQty" type="number" name="children" min="0" value="0"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-gray-700">Trẻ nhỏ (2–4 tuổi)</label>
                                    <input id="infantQty" type="number" name="infants" min="0" value="0"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-gray-700">Em bé (dưới 2 tuổi)</label>
                                    <input id="youthQty" type="number" name="youths" min="0" value="0"
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                                </div>
                            </div>
                        </div>

                        <!-- THÔNG TIN HÀNH KHÁCH -->
                        <div class="mt-2 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-700">Thông tin hành khách</span>
                                <span class="text-xs text-gray-500">Tự động sinh theo số lượng khách</span>
                            </div>
                            <div id="passengerContainer" class="space-y-3"></div>
                        </div>

                        <!-- GHI CHÚ -->
                        <div>
                            <label class="text-sm font-semibold text-gray-700">Ghi chú thêm</label>
                            <textarea name="note" rows="3"
                                class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none"
                                placeholder="Yêu cầu đặc biệt, lưu ý về ăn uống, phòng ở..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: SUMMARY -->
                <aside class="sticky top-24 lg:col-span-4">
                    <div class="space-y-4 top-24">
                        <div class="bg-white rounded-2xl border border-gray-200 p-5">
                            <h2 class="text-lg font-bold text-gray-900 mb-4">Thông tin giá</h2>

                            <!-- LIST ĐƠN GIÁ THEO SỐ LƯỢNG CHỌN -->
                            <div class="space-y-1 text-sm mb-3">
                                <div id="adultRow" class="flex items-center justify-between">
                                    <span class="text-gray-700">Người lớn</span>
                                    <span id="adultBreakdown" class="font-semibold text-gray-900">0 đ</span>
                                </div>
                                <div id="childRow" class="flex items-center justify-between">
                                    <span class="text-gray-700">Trẻ em</span>
                                    <span id="childBreakdown" class="font-semibold text-gray-900">0 đ</span>
                                </div>
                                <div id="infantRow" class="flex items-center justify-between">
                                    <span class="text-gray-700">Trẻ nhỏ</span>
                                    <span id="infantBreakdown" class="font-semibold text-gray-900">0 đ</span>
                                </div>
                                <div id="youthRow" class="flex items-center justify-between">
                                    <span class="text-gray-700">Em bé</span>
                                    <span id="youthBreakdown" class="font-semibold text-gray-900">0 đ</span>
                                </div>
                                <div id="singleRow" class="flex items-center justify-between pt-1 border-t border-dashed border-gray-200 mt-1">
                                    <span class="text-gray-700">Phụ thu phòng đơn</span>
                                    <span id="singleBreakdown" class="font-semibold text-gray-900">0 đ</span>
                                </div>
                            </div>

                            <div class="mt-4 border-t border-gray-200 pt-4 text-sm space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Ngày khởi hành</span>
                                    <span class="font-semibold text-gray-900">
                                        {{ Carbon::parse($departure->start_date)->format('d/m/Y') }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Số chỗ còn lại</span>
                                    <span id="seatLeftDisplay" class="font-semibold text-red-600">{{ $seatLeft }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Điểm khởi hành</span>
                                    <span class="font-semibold text-gray-900 text-right">
                                        {{ $tour->departure_location }}
                                    </span>
                                </div>
                            </div>

                            <!-- TẠM TÍNH -->
                            <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Tạm tính</span>
                                    <b id="totalPrice" class="text-lg text-gray-900">0 đ</b>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    * Số tiền thực tế có thể thay đổi nếu chương trình khuyến mãi hoặc phụ thu thêm.
                                </div>
                            </div>

                            <!-- HÌNH THỨC THANH TOÁN -->
                            <div class="mt-3">
                                <label class="text-sm font-semibold text-gray-700">Hình thức thanh toán</label>
                                <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                    <label class="flex items-start gap-2 rounded-xl border border-gray-200 bg-white p-3 cursor-pointer hover:border-sky-500">
                                        <input type="radio" name="payment_mode" value="full" class="mt-1 text-sky-600" checked>
                                        <div>
                                            <div class="font-semibold text-gray-900">Thanh toán toàn bộ</div>
                                            <div class="text-xs text-gray-500">Thanh toán 100% giá trị đơn tour qua VNPay.</div>
                                        </div>
                                    </label>
                                    <label class="flex items-start gap-2 rounded-xl border border-gray-200 bg-white p-3 cursor-pointer hover:border-sky-500">
                                        <input type="radio" name="payment_mode" value="deposit" class="mt-1 text-sky-600">
                                        <div>
                                            <div class="font-semibold text-gray-900">Đặt cọc 30%</div>
                                            <div class="text-xs text-gray-500">Thanh toán trước 30% giá trị đơn, phần còn lại thanh toán sau.</div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- SUBMIT -->
                            <div class="pt-3 mt-2">
                                <button type="submit"
                                    class="w-full rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 focus:ring-4 focus:ring-blue-200">
                                    Thanh toán VNPay
                                </button>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </form>

        <script>
            const adultQty = document.getElementById('adultQty');
            const childQty = document.getElementById('childQty');
            const infantQty = document.getElementById('infantQty');
            const youthQty = document.getElementById('youthQty');
            const totalPriceEl = document.getElementById('totalPrice');
            const passengerContainer = document.getElementById('passengerContainer');

            const adultRow = document.getElementById('adultRow');
            const childRow = document.getElementById('childRow');
            const infantRow = document.getElementById('infantRow');
            const youthRow = document.getElementById('youthRow');
            const singleRow = document.getElementById('singleRow');

            const adultBreakdown = document.getElementById('adultBreakdown');
            const childBreakdown = document.getElementById('childBreakdown');
            const infantBreakdown = document.getElementById('infantBreakdown');
            const youthBreakdown = document.getElementById('youthBreakdown');
            const singleBreakdown = document.getElementById('singleBreakdown');

            const priceAdult = {{ (int) $departure->price_adult }};
            const priceChild = {{ (int) $departure->price_child }};
            const priceInfant = {{ (int) $departure->price_infant }};
            const priceYouth = {{ (int) $departure->price_youth }};
            const singleSurcharge = {{ (int) $departure->single_room_surcharge }};
            const maxGuests = {{ (int) $seatLeft }};
            const departureDate = new Date('{{ \Carbon\Carbon::parse($departure->start_date)->format('Y-m-d') }}');
            const seatLeftDisplay = document.getElementById('seatLeftDisplay');

            function formatVND(number) {
                return new Intl.NumberFormat('vi-VN').format(number) + ' đ';
            }

            function calculateAgeYears(dobStr, refDate) {
                const dob = new Date(dobStr);
                if (Number.isNaN(dob.getTime())) return null;
                let age = refDate.getFullYear() - dob.getFullYear();
                const m = refDate.getMonth() - dob.getMonth();
                if (m < 0 || (m === 0 && refDate.getDate() < dob.getDate())) {
                    age--;
                }
                return age;
            }

            function getInputValue(input, minValue = 0) {
                const parsed = parseInt(input.value, 10);
                if (Number.isNaN(parsed)) {
                    return minValue;
                }
                return Math.max(parsed, minValue);
            }

            function getCurrentGuestTotal() {
                const counts = getGuestCounts();
                return counts.adults + counts.children + counts.infants + counts.youths;
            }

            function getGuestCounts() {
                return {
                    adults: getInputValue(adultQty, 1),
                    children: getInputValue(childQty),
                    infants: getInputValue(infantQty),
                    youths: getInputValue(youthQty),
                };
            }

            function updateSeatLeftDisplay() {
                if (!seatLeftDisplay) return;
                seatLeftDisplay.innerText = maxGuests - getCurrentGuestTotal();
            }

            function syncPreviousGuestValues() {
                const inputs = [adultQty, childQty, infantQty, youthQty];

                inputs.forEach((input) => {
                    input.dataset.prevValue = input.value;
                });
            }

            function enforceSeatLimit(changedInput = null) {
                const inputs = [adultQty, childQty, infantQty, youthQty];

                inputs.forEach((input) => {
                    const minValue = input === adultQty ? 1 : 0;
                    input.value = String(getInputValue(input, minValue));
                });

                const remainingSeats = maxGuests - getCurrentGuestTotal();
                if (remainingSeats >= 0) {
                    updateSeatLeftDisplay();
                    syncPreviousGuestValues();
                    return true;
                }

                if (changedInput) {
                    const minValue = changedInput === adultQty ? 1 : 0;
                    const previousValue = parseInt(changedInput.dataset.prevValue ?? minValue, 10);
                    changedInput.value = String(Number.isNaN(previousValue) ? minValue : Math.max(previousValue, minValue));
                }

                updateSeatLeftDisplay();
                alert(`Số lượng khách vượt quá số chỗ còn lại. Tour chỉ còn ${maxGuests} chỗ.`);
                return false;
            }

            function validateAgeForRow(row) {
                const dobInput = row.querySelector('input[name*="[dob]"]');
                const typeInput = row.querySelector('input[name*="[passenger_type]"]');
                if (!dobInput || !typeInput) return true;

                const dob = dobInput.value;
                const type = typeInput.value;

                dobInput.setCustomValidity('');

                if (!dob) {
                    dobInput.setCustomValidity('Vui lòng nhập ngày sinh.');
                    return false;
                }

                const age = calculateAgeYears(dob, departureDate);
                if (age === null) {
                    dobInput.setCustomValidity('Ngày sinh không hợp lệ.');
                    return false;
                }

                switch (type) {
                    case 'adult':
                        if (age < 12) {
                            dobInput.setCustomValidity('Người lớn phải từ 12 tuổi trở lên tính đến ngày khởi hành.');
                            return false;
                        }
                        break;
                    case 'child':
                        if (age < 5 || age > 11) {
                            dobInput.setCustomValidity('Trẻ em phải từ 5 đến 11 tuổi tính đến ngày khởi hành.');
                            return false;
                        }
                        break;
                    case 'infant':
                        if (age < 2 || age > 4) {
                            dobInput.setCustomValidity('Trẻ nhỏ phải từ 2 đến 4 tuổi tính đến ngày khởi hành.');
                            return false;
                        }
                        break;
                    case 'youth':
                        if (age >= 2) {
                            dobInput.setCustomValidity('Em bé phải dưới 2 tuổi tính đến ngày khởi hành.');
                            return false;
                        }
                        break;
                    default:
                        break;
                }

                return true;
            }

            function renderPassengers() {
                if (!passengerContainer) return;

                const {
                    adults,
                    children,
                    infants,
                    youths,
                } = getGuestCounts();

                passengerContainer.innerHTML = '';

                let index = 0;

                function addGroup(label, subtitle, type, count) {
                    if (count <= 0) return;

                    const group = document.createElement('div');
                    group.className = 'border-t border-gray-200 pt-4 mt-4 first:mt-0 first:border-t-0';
                    group.innerHTML = `
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2 text-sm font-semibold text-gray-900">
                                <span>${label}</span>
                                <span class="text-xs font-normal text-gray-500">${subtitle}</span>
                            </div>
                        </div>
                    `;

                    const rows = document.createElement('div');
                    rows.className = 'space-y-3';

                    for (let i = 0; i < count; i++) {
                        const row = document.createElement('div');
                        row.className = 'grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-3 text-sm items-end';
                        row.setAttribute('data-passenger-row', '1');
                        row.innerHTML = `
                            <div>
                                <label class="text-xs text-gray-600">Họ tên *</label>
                                <input type="text" name="passengers[${index}][full_name]" required
                                    class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                            </div>
                            <div>
                                <label class="text-xs text-gray-600">Giới tính *</label>
                                <select name="passengers[${index}][gender]"
                                    class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm bg-white focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                                    <option value="male">Nam</option>
                                    <option value="female">Nữ</option>
                                    <option value="other">Khác</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-gray-600">Ngày sinh *</label>
                                <input type="date" name="passengers[${index}][dob]" required
                                    class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:ring-4 focus:ring-sky-100 focus:border-sky-500 outline-none">
                            </div>
                            ${type === 'adult'
                                ? `<div class="flex flex-col gap-1 text-xs text-gray-700">
                                        <label class="inline-flex items-center justify-between gap-2">
                                            <span>Phòng đơn</span>
                                            <input type="checkbox" name="passengers[${index}][single_room]" value="1" data-single-room="1"
                                                class="rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                                        </label>
                                        <span class="text-[11px] text-gray-500">Phụ thu: ${formatVND(singleSurcharge)}</span>
                                   </div>`
                                : ''}
                            <input type="hidden" name="passengers[${index}][passenger_type]" value="${type}">
                        `;
                        rows.appendChild(row);
                        index++;
                    }

                    group.appendChild(rows);
                    passengerContainer.appendChild(group);
                }

                addGroup('Người lớn', '(Từ 12 trở lên)', 'adult', adults);
                addGroup('Trẻ em', '(Từ 5 - 11 tuổi)', 'child', children);
                addGroup('Trẻ nhỏ', '(Từ 2 - 4 tuổi)', 'infant', infants);
                addGroup('Em bé', '(Dưới 2 tuổi)', 'youth', youths);
            }

            function updateTotal() {
                const {
                    adults,
                    children,
                    infants,
                    youths,
                } = getGuestCounts();

                let total = adults * priceAdult + children * priceChild + infants * priceInfant + youths * priceYouth;

                // Phụ thu phòng đơn theo từng người lớn
                let singleCount = 0;
                if (passengerContainer && singleSurcharge > 0) {
                    const singleRoomCheckboxes = passengerContainer.querySelectorAll('input[data-single-room="1"]');
                    singleRoomCheckboxes.forEach(cb => {
                        if (cb.checked) singleCount++;
                    });
                    total += singleCount * singleSurcharge;
                }

                // Cập nhật list breakdown bên phải
                if (adultRow && adultBreakdown) {
                    if (adults > 0) {
                        adultRow.style.display = 'flex';
                        adultBreakdown.innerText = `${adults} x ${formatVND(priceAdult)}`;
                    } else {
                        adultRow.style.display = 'none';
                    }
                }

                if (childRow && childBreakdown) {
                    if (children > 0) {
                        childRow.style.display = 'flex';
                        childBreakdown.innerText = `${children} x ${formatVND(priceChild)}`;
                    } else {
                        childRow.style.display = 'none';
                    }
                }

                if (infantRow && infantBreakdown) {
                    if (infants > 0) {
                        infantRow.style.display = 'flex';
                        infantBreakdown.innerText = `${infants} x ${formatVND(priceInfant)}`;
                    } else {
                        infantRow.style.display = 'none';
                    }
                }

                if (youthRow && youthBreakdown) {
                    if (youths > 0) {
                        youthRow.style.display = 'flex';
                        youthBreakdown.innerText = `${youths} x ${formatVND(priceYouth)}`;
                    } else {
                        youthRow.style.display = 'none';
                    }
                }

                if (singleRow && singleBreakdown) {
                    singleRow.style.display = 'flex';
                    singleBreakdown.innerText = formatVND(singleCount * singleSurcharge);
                }

                totalPriceEl.innerText = formatVND(total);
            }

            function handleGuestQuantityChange(input) {
                enforceSeatLimit(input);
                renderPassengers();
                updateTotal();
            }

            adultQty.addEventListener('input', () => handleGuestQuantityChange(adultQty));
            childQty.addEventListener('input', () => handleGuestQuantityChange(childQty));
            infantQty.addEventListener('input', () => handleGuestQuantityChange(infantQty));
            youthQty.addEventListener('input', () => handleGuestQuantityChange(youthQty));

            if (passengerContainer) {
                passengerContainer.addEventListener('change', (e) => {
                    const target = e.target;
                    const row = target.closest('[data-passenger-row]');
                    if (row) {
                        validateAgeForRow(row);
                    }
                    if (target && target.matches('input[data-single-room="1"]')) {
                        updateTotal();
                    }
                });
            }

            const bookingForm = document.getElementById('bookingForm');
            if (bookingForm && passengerContainer) {
                bookingForm.addEventListener('submit', (e) => {
                    if (!enforceSeatLimit()) {
                        e.preventDefault();
                        return;
                    }

                    const rows = passengerContainer.querySelectorAll('[data-passenger-row]');
                    let allValid = true;
                    rows.forEach((row) => {
                        const ok = validateAgeForRow(row);
                        if (!ok && allValid) {
                            const dobInput = row.querySelector('input[name*="[dob]"]');
                            if (dobInput) {
                                dobInput.reportValidity();
                            }
                        }
                        allValid = allValid && ok;
                    });

                    if (!allValid) {
                        e.preventDefault();
                    }
                });
            }

            enforceSeatLimit();
            renderPassengers();
            updateTotal();
        </script>
    </section>
@endsection
