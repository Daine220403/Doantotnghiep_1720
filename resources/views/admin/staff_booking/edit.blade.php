@php
    $order = $order ?? $booking->order;
    $departure = $booking->departure;
    $tour = $departure ? $departure->tour : null;
    $passengers = $booking->passengers;
@endphp

@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Cập nhật booking BK{{ $booking->id }}</h1>
                <p class="mb-0 text-muted small">
                    Nhân viên có thể cập nhật lại thông tin liên hệ và danh sách hành khách của booking này.
                </p>
            </div>
            <a href="{{ route('admin.staff-booking.tours.show', optional(optional($departure)->tour)->id ?? $booking->departure->tour_id) }}"
                class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Quay lại tour
            </a>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('admin.staff-booking.update', $booking->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <h6 class="font-weight-bold text-primary mb-2">Thông tin tour</h6>
                            @if ($tour)
                                <p class="mb-1 font-weight-bold">{{ $tour->title }}</p>
                                <p class="mb-1 small text-muted">{{ $tour->destination_text }}</p>
                                <p class="mb-1 small"><span class="text-muted">Mã tour:</span> {{ $tour->code }}</p>
                                <p class="mb-1 small"><span class="text-muted">Điểm khởi hành:</span>
                                    {{ $tour->departure_location }}</p>
                                <p class="mb-1 small"><span class="text-muted">Thời gian:</span>
                                    {{ $tour->duration_days }}N{{ $tour->duration_nights }}Đ</p>
                            @else
                                <p class="small text-muted">Tour đã bị ẩn hoặc không còn khả dụng.</p>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="font-weight-bold text-primary mb-2">Thông tin lịch khởi hành</h6>
                            @if ($departure)
                                <p class="mb-1 small"><span class="text-muted">Ngày khởi hành:</span>
                                    {{ \Carbon\Carbon::parse($departure->start_date)->format('d/m/Y') }}</p>
                                <p class="mb-1 small"><span class="text-muted">Ngày kết thúc:</span>
                                    {{ \Carbon\Carbon::parse($departure->end_date)->format('d/m/Y') }}</p>
                                <p class="mb-1 small"><span class="text-muted">Điểm tập trung:</span>
                                    {{ $departure->meeting_point }}</p>
                            @else
                                <p class="small text-muted">Không tìm thấy thông tin lịch khởi hành.</p>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <h6 class="font-weight-bold text-primary mb-2">Thông tin liên hệ</h6>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Tên liên hệ *</label>
                                <input type="text" name="contact_name"
                                    value="{{ old('contact_name', optional($order)->contact_name) }}"
                                    class="form-control form-control-sm @error('contact_name') is-invalid @enderror">
                                @error('contact_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Số điện thoại *</label>
                                <input type="text" name="contact_phone"
                                    value="{{ old('contact_phone', optional($order)->contact_phone) }}"
                                    class="form-control form-control-sm @error('contact_phone') is-invalid @enderror">
                                @error('contact_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Email *</label>
                                <input type="email" name="contact_email"
                                    value="{{ old('contact_email', optional($order)->contact_email) }}"
                                    class="form-control form-control-sm @error('contact_email') is-invalid @enderror">
                                @error('contact_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Ghi chú</label>
                                <textarea name="note" rows="3" class="form-control form-control-sm @error('note') is-invalid @enderror">{{ old('note', $booking->note) }}</textarea>
                                @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="font-weight-bold text-primary mb-2">Thông tin thanh toán</h6>
                            @if ($order)
                                <p class="mb-1 small"><span class="text-muted">Mã đơn hàng:</span>
                                    {{ $order->order_code }}</p>
                                <p class="mb-1 small"><span class="text-muted">Tổng tiền:</span>
                                    {{ number_format($order->total_amount, 0, ',', '.') }} đ</p>
                                <p class="mb-1 small"><span class="text-muted">Đã thanh toán:</span>
                                    {{ number_format($paidAmount ?? 0, 0, ',', '.') }} đ</p>
                                <p class="mb-1 small"><span class="text-muted">Trạng thái đơn:</span>
                                    {{ $order->status }}</p>
                            @else
                                <p class="small text-muted">Không có thông tin đơn hàng.</p>
                            @endif

                            {{-- Tạm tính giống trang tạo booking --}}
                            @if ($departure)
                                <div class="card shadow-sm mt-3">
                                    <div class="card-header py-2">
                                        <h6 class="m-0 font-weight-bold text-primary">Tạm tính</h6>
                                    </div>
                                    <div class="card-body small" id="summaryBody">
                                        <p class="mb-1 text-muted">Chưa có hành khách nào.</p>
                                        <hr class="my-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="font-weight-bold">Tổng tiền tạm tính:</span>
                                            <span class="h5 text-danger mb-0">0 đ</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <h6 class="font-weight-bold text-primary mb-2">Danh sách hành khách</h6>
                    @error('passengers')
                        <div class="alert alert-danger small py-1">{{ $message }}</div>
                    @enderror

                    <div id="passengerContainer" class="mb-3">
                        @foreach ($passengers as $index => $p)
                            @php
                                $currentType = old('passengers.' . $index . '.passenger_type', $p->passenger_type);
                            @endphp
                            <div class="border rounded p-2 mb-2" data-passenger-row>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="small font-weight-bold">
                                        Hành khách <span data-passenger-index>{{ $index + 1 }}</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <select name="passengers[{{ $index }}][passenger_type]"
                                            class="form-control form-control-sm mr-2" style="width:auto;">
                                            <option value="adult" {{ $currentType === 'adult' ? 'selected' : '' }}>Người
                                                lớn</option>
                                            <option value="child" {{ $currentType === 'child' ? 'selected' : '' }}>Trẻ em
                                            </option>
                                            <option value="infant" {{ $currentType === 'infant' ? 'selected' : '' }}>Trẻ
                                                nhỏ</option>
                                            <option value="youth" {{ $currentType === 'youth' ? 'selected' : '' }}>Em bé
                                            </option>
                                        </select>
                                        <button type="button" class="btn btn-outline-danger btn-sm" data-remove-passenger>
                                            Xóa
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" name="passengers[{{ $index }}][id]"
                                    value="{{ $p->id }}">
                                <div class="form-row small">
                                    <div class="form-group col-md-4 mb-2">
                                        <label class="mb-1">Họ tên *</label>
                                        <input type="text" name="passengers[{{ $index }}][full_name]"
                                            value="{{ old('passengers.' . $index . '.full_name', $p->full_name) }}"
                                            class="form-control form-control-sm">
                                    </div>
                                    <div class="form-group col-md-3 mb-2">
                                        <label class="mb-1">Giới tính</label>
                                        <select name="passengers[{{ $index }}][gender]"
                                            class="form-control form-control-sm">
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
                                    <div class="form-group col-md-3 mb-2">
                                        <label class="mb-1">Ngày sinh</label>
                                        <input type="date" name="passengers[{{ $index }}][dob]"
                                            value="{{ old('passengers.' . $index . '.dob', $p->dob ? \Carbon\Carbon::parse($p->dob)->format('Y-m-d') : '') }}"
                                            class="form-control form-control-sm">
                                    </div>
                                    <div class="form-group col-md-2 mb-2 align-self-end">
                                        <div class="form-check small {{ $currentType === 'adult' ? '' : 'd-none' }}"
                                            data-single-room-wrapper>
                                            <input class="form-check-input" type="checkbox"
                                                name="passengers[{{ $index }}][single_room]" value="1"
                                                {{ old('passengers.' . $index . '.single_room', $p->single_room) ? 'checked' : '' }}>
                                            <label class="form-check-label">Phòng đơn</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-3 small">
                        <button type="button" class="btn btn-sm btn-outline-primary mr-1" data-add-passenger="adult">+
                            Người lớn</button>
                        <button type="button" class="btn btn-sm btn-outline-primary mr-1" data-add-passenger="child">+
                            Trẻ em</button>
                        <button type="button" class="btn btn-sm btn-outline-primary mr-1" data-add-passenger="infant">+
                            Trẻ nhỏ</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-add-passenger="youth">+ Em
                            bé</button>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <a href="{{ route('admin.staff-booking.tours.show', optional(optional($departure)->tour)->id ?? $booking->departure->tour_id) }}"
                            class="btn btn-light btn-sm mr-2">Hủy</a>
                        <button type="submit" class="btn btn-primary btn-sm">Lưu thay đổi</button>
                    </div>
                </form>
            </div>
            {{-- hiện lỗi ra đây --}}
            @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    <strong>Dữ liệu không hợp lệ:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <script>
        (function() {
            const container = document.getElementById('passengerContainer');
            if (!container) return;

            const summaryBody = document.getElementById('summaryBody');

            // Giá từ server để dùng tính tạm tính trên giao diện (giống trang create)
            const priceAdult = {{ (float) ($departure->price_adult ?? 0) }};
            const priceChild = {{ (float) ($departure->price_child ?? 0) }};
            const priceInfant = {{ (float) ($departure->price_infant ?? 0) }};
            const priceYouth = {{ (float) ($departure->price_youth ?? 0) }};
            const singleRoomPrice = {{ (float) ($departure->single_room_surcharge ?? 0) }};

            let index = {{ $passengers->count() }};

            function formatCurrency(value) {
                const v = Number(value) || 0;
                return v.toLocaleString('vi-VN') + ' đ';
            }

            function updateSummary() {
                if (!summaryBody) return;

                const rows = container.querySelectorAll('[data-passenger-row]');
                let adultCount = 0;
                let childCount = 0;
                let infantCount = 0;
                let youthCount = 0;
                let singleRooms = 0;

                rows.forEach((row) => {
                    const typeSelect = row.querySelector('select[name*="[passenger_type]"]');
                    const type = typeSelect ? typeSelect.value : 'adult';
                    const checkbox = row.querySelector('input[type="checkbox"][name*="[single_room]"]');

                    if (type === 'adult') adultCount++;
                    else if (type === 'child') childCount++;
                    else if (type === 'infant') infantCount++;
                    else if (type === 'youth') youthCount++;

                    const isSingle = type === 'adult' && checkbox && checkbox.checked;
                    if (isSingle) singleRooms++;
                });

                const adultTotal = adultCount * priceAdult;
                const childTotal = childCount * priceChild;
                const infantTotal = infantCount * priceInfant;
                const youthTotal = youthCount * priceYouth;
                const singleTotal = singleRooms * singleRoomPrice;
                const grandTotal = adultTotal + childTotal + infantTotal + youthTotal + singleTotal;

                let html = '';

                if (rows.length === 0) {
                    html += '<p class="mb-1 text-muted">Chưa có hành khách nào.</p>';
                } else {
                    if (adultCount > 0) {
                        html += `<div class="d-flex justify-content-between mb-1">
                            <span>Người lớn</span>
                            <span>${adultCount} x ${formatCurrency(priceAdult)} = <strong>${formatCurrency(adultTotal)}</strong></span>
                        </div>`;
                    }
                    if (childCount > 0) {
                        html += `<div class="d-flex justify-content-between mb-1">
                            <span>Trẻ em</span>
                            <span>${childCount} x ${formatCurrency(priceChild)} = <strong>${formatCurrency(childTotal)}</strong></span>
                        </div>`;
                    }
                    if (infantCount > 0) {
                        html += `<div class="d-flex justify-content-between mb-1">
                            <span>Trẻ nhỏ</span>
                            <span>${infantCount} x ${formatCurrency(priceInfant)} = <strong>${formatCurrency(infantTotal)}</strong></span>
                        </div>`;
                    }
                    if (youthCount > 0) {
                        html += `<div class="d-flex justify-content-between mb-1">
                            <span>Em bé</span>
                            <span>${youthCount} x ${formatCurrency(priceYouth)} = <strong>${formatCurrency(youthTotal)}</strong></span>
                        </div>`;
                    }
                    if (singleRooms > 0 && singleRoomPrice > 0) {
                        html += `<div class="d-flex justify-content-between mb-1">
                            <span>Phụ thu phòng đơn</span>
                            <span>${singleRooms} x ${formatCurrency(singleRoomPrice)} = <strong>${formatCurrency(singleTotal)}</strong></span>
                        </div>`;
                    }
                }

                html += '<hr class="my-2">';
                html += `<div class="d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold">Tổng tiền tạm tính:</span>
                    <span class="h5 text-danger mb-0">${formatCurrency(grandTotal)}</span>
                </div>`;

                summaryBody.innerHTML = html;
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

                updateSummary();
            }

            function updateSingleRoomVisibility(row) {
                if (!row) return;
                const typeSelect = row.querySelector('select[name*="[passenger_type]"]');
                const wrapper = row.querySelector('[data-single-room-wrapper]');
                if (!typeSelect || !wrapper) return;

                const type = typeSelect.value;
                const checkbox = wrapper.querySelector('input[type="checkbox"]');

                if (type === 'adult') {
                    wrapper.classList.remove('d-none');
                } else {
                    wrapper.classList.add('d-none');
                    if (checkbox) {
                        checkbox.checked = false;
                    }
                }
            }

            function createRow(type) {
                const row = document.createElement('div');
                row.className = 'border rounded p-2 mb-2';
                row.setAttribute('data-passenger-row', '');

                row.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="small font-weight-bold">
                            Hành khách <span data-passenger-index></span>
                        </div>
                        <div class="d-flex align-items-center">
                            <select name="passengers[${index}][passenger_type]"
                                class="form-control form-control-sm mr-2" style="width:auto;">
                                <option value="adult" ${type === 'adult' ? 'selected' : ''}>Người lớn</option>
                                <option value="child" ${type === 'child' ? 'selected' : ''}>Trẻ em</option>
                                <option value="infant" ${type === 'infant' ? 'selected' : ''}>Trẻ nhỏ</option>
                                <option value="youth" ${type === 'youth' ? 'selected' : ''}>Em bé</option>
                            </select>
                            <button type="button" class="btn btn-outline-danger btn-sm" data-remove-passenger>
                                Xóa
                            </button>
                        </div>
                    </div>
                    <div class="form-row small">
                        <div class="form-group col-md-4 mb-2">
                            <label class="mb-1">Họ tên *</label>
                            <input type="text" name="passengers[${index}][full_name]"
                                class="form-control form-control-sm">
                        </div>
                        <div class="form-group col-md-3 mb-2">
                            <label class="mb-1">Giới tính</label>
                            <select name="passengers[${index}][gender]"
                                class="form-control form-control-sm">
                                <option value="male">Nam</option>
                                <option value="female">Nữ</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3 mb-2">
                            <label class="mb-1">Ngày sinh</label>
                            <input type="date" name="passengers[${index}][dob]"
                                class="form-control form-control-sm">
                        </div>
                        <div class="form-group col-md-2 mb-2 align-self-end">
                            <div class="form-check small ${type === 'adult' ? '' : 'd-none'}" data-single-room-wrapper>
                                <input class="form-check-input" type="checkbox"
                                    name="passengers[${index}][single_room]" value="1">
                                <label class="form-check-label">Phòng đơn</label>
                            </div>
                        </div>
                    </div>
                `;

                container.appendChild(row);
                index++;
                updateIndexes();
                updateSingleRoomVisibility(row);
            }

            // Khởi tạo
            updateIndexes();
            container.querySelectorAll('[data-passenger-row]').forEach(updateSingleRoomVisibility);
            updateSummary();

            // Thêm khách
            document.querySelectorAll('[data-add-passenger]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const type = btn.getAttribute('data-add-passenger') || 'adult';
                    createRow(type);
                });
            });

            // Xóa khách
            container.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-remove-passenger]');
                if (!btn) return;
                const row = btn.closest('[data-passenger-row]');
                if (row) {
                    row.remove();
                    updateIndexes();
                }
            });

            // Đổi loại khách
            container.addEventListener('change', (e) => {
                const row = e.target.closest('[data-passenger-row]');
                if (!row) return;

                const select = e.target.closest('select[name*="[passenger_type]"]');
                if (select) {
                    updateSingleRoomVisibility(row);
                }

                // Bất kỳ thay đổi loại khách hay phòng đơn đều cập nhật tạm tính
                updateSummary();
            });
        })();
    </script>
@endsection
