@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Đặt tour cho khách</h1>
                <p class="mb-0 text-muted">
                    Nhân viên nhập thông tin khách liên hệ và danh sách hành khách để tạo booking offline (chưa thanh toán).
                </p>
            </div>
            <a href="{{ route('admin.staff-booking.tours.show', $tour->id) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Quay lại tour
            </a>
        </div>

        <form action="{{ route('admin.staff-booking.store') }}" method="POST">
            @csrf
            <input type="hidden" name="tour_id" value="{{ $tour->id }}">
            <input type="hidden" name="departure_id" value="{{ $departure->id }}">

            <div class="row">
                <div class="col-lg-5 mb-4">
                    {{-- Thông tin tour & lịch khởi hành --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header py-2">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin tour & lịch khởi hành</h6>
                        </div>
                        <div class="card-body small">
                            <p class="mb-2"><span class="text-muted">Tên tour:</span>
                                <span class="font-weight-bold">{{ $tour->title }}</span>
                            </p>
                            <p class="mb-2"><span class="text-muted">Mã tour:</span> {{ $tour->code }}</p>
                            <p class="mb-2"><span class="text-muted">Điểm đi:</span> {{ $tour->departure_location }}</p>
                            <p class="mb-2"><span class="text-muted">Điểm đến:</span> {{ $tour->destination_text }}</p>
                            <p class="mb-2"><span class="text-muted">Thời gian:</span>
                                {{ $tour->duration_days }}N{{ $tour->duration_nights }}Đ
                            </p>
                            <hr>
                            <p class="mb-2"><span class="text-muted">Ngày khởi hành:</span>
                                {{ \Carbon\Carbon::parse($departure->start_date)->format('d/m/Y') }}
                            </p>
                            <p class="mb-2"><span class="text-muted">Ngày kết thúc:</span>
                                {{ \Carbon\Carbon::parse($departure->end_date)->format('d/m/Y') }}
                            </p>
                            <p class="mb-2"><span class="text-muted">Điểm tập trung:</span>
                                {{ $departure->meeting_point ?? $tour->departure_location }}
                            </p>
                            @php
                                $seatLeft = max(
                                    ($departure->capacity_total ?? 0) - ($departure->capacity_booked ?? 0),
                                    0,
                                );
                            @endphp
                            <p class="mb-0"><span class="text-muted">Số chỗ còn lại:</span>
                                <strong class="text-danger">{{ $seatLeft }}</strong>
                            </p>
                        </div>
                    </div>

                    {{-- Thông tin khách liên hệ --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header py-2">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin khách liên hệ</h6>
                        </div>
                        <div class="card-body small">
                            <div class="form-group mb-2">
                                <label class="mb-0">Họ tên khách liên hệ <span class="text-danger">*</span></label>
                                <input type="text" name="contact_name" value="{{ old('contact_name') }}"
                                    class="form-control form-control-sm @error('contact_name') is-invalid @enderror">
                                @error('contact_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mb-2">
                                <label class="mb-0">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="text" name="contact_phone" value="{{ old('contact_phone') }}"
                                    class="form-control form-control-sm @error('contact_phone') is-invalid @enderror">
                                @error('contact_phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mb-2">
                                <label class="mb-0">Email</label>
                                <input type="email" name="contact_email" value="{{ old('contact_email') }}"
                                    class="form-control form-control-sm @error('contact_email') is-invalid @enderror">
                                @error('contact_email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mb-0">
                                <label class="mb-0">Ghi chú thêm</label>
                                <textarea name="note" rows="3" class="form-control form-control-sm">{{ old('note') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7 mb-4">
                    {{-- Thông tin giá tour --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header py-2">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin giá tour</h6>
                        </div>
                        <div class="card-body small">
                            <p class="mb-2">
                                <span class="text-muted">Giá người lớn:</span>
                                <strong>{{ number_format($departure->price_adult, 0, ',', '.') }} đ/người</strong>
                            </p>
                            @if ($departure->price_child)
                                <p class="mb-2">
                                    <span class="text-muted">Giá trẻ em (5–11 tuổi):</span>
                                    <strong>{{ number_format($departure->price_child, 0, ',', '.') }} đ/người</strong>
                                </p>
                            @endif
                            @if ($departure->price_infant)
                                <p class="mb-2">
                                    <span class="text-muted">Giá trẻ nhỏ (2–4 tuổi):</span>
                                    <strong>{{ number_format($departure->price_infant, 0, ',', '.') }} đ/người</strong>
                                </p>
                            @endif
                            @if ($departure->price_youth)
                                <p class="mb-2">
                                    <span class="text-muted">Giá em bé (dưới 2 tuổi):</span>
                                    <strong>{{ number_format($departure->price_youth, 0, ',', '.') }} đ/người</strong>
                                </p>
                            @endif

                            @if ($departure->single_room_surcharge)
                                <hr>
                                <p class="mb-0">
                                    <span class="text-muted">Phụ thu phòng đơn (mỗi khách người lớn):</span>
                                    <strong>{{ number_format($departure->single_room_surcharge, 0, ',', '.') }}
                                        đ/khách</strong>
                                </p>
                            @endif
                        </div>
                    </div>
                    {{-- Tạm tính tổng tiền --}}
                    <div class="card shadow-sm mb-4">
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
                    {{-- Thông tin hành khách --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                            <h6 class="m-0 font-weight-bold text-primary">Danh sách hành khách</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addPassengerBtn">
                                <i class="fas fa-user-plus"></i> Thêm hành khách
                            </button>
                        </div>
                        <div class="card-body small">
                            <p class="text-muted small">Mặc định hệ thống thêm 1 hành khách người lớn. Nhân viên có thể
                                thêm/bớt
                                khách theo nhu cầu.</p>

                            <div id="passengerList">
                                {{-- Hàng hành khách sẽ được thêm bằng JavaScript --}}
                            </div>

                            @error('passengers')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                            {{-- Lỗi chi tiết cho từng trường trong mảng passengers --}}
                            @foreach ($errors->get('passengers.*.full_name') as $msg)
                                <div class="text-danger small mt-1">Họ tên hành khách: {{ $msg[0] }}</div>
                            @endforeach
                            @foreach ($errors->get('passengers.*.passenger_type') as $msg)
                                <div class="text-danger small mt-1">Loại khách: {{ $msg[0] }}</div>
                            @endforeach
                            @foreach ($errors->get('passengers.*.gender') as $msg)
                                <div class="text-danger small mt-1">Giới tính: {{ $msg[0] }}</div>
                            @endforeach
                            @foreach ($errors->get('passengers.*.single_room') as $msg)
                                <div class="text-danger small mt-1">Phòng đơn: {{ $msg[0] }}</div>
                            @endforeach
                            @foreach ($errors->get('passengers.*.dob') as $msg)
                                <div class="text-danger small mt-1">Ngày sinh: {{ $msg[0] }}</div>
                            @endforeach
                            @foreach ($errors->get('passengers.*.id_no') as $msg)
                                <div class="text-danger small mt-1">Số giấy tờ: {{ $msg[0] }}</div>
                            @endforeach
                        </div>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu booking cho khách
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <template id="passengerRowTemplate">
        <div class="border rounded p-2 mb-2 passenger-row">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong class="mb-0">Hành khách <span class="passenger-index"></span></strong>
                <button type="button" class="btn btn-xs btn-link text-danger p-0 remove-passenger-btn">Xoá</button>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4 mb-2">
                    <label class="mb-0">Họ tên *</label>
                    <input type="text" class="form-control form-control-sm passenger-full-name">
                </div>
                <div class="form-group col-md-3 mb-2">
                    <label class="mb-0">Loại khách *</label>
                    <select class="form-control form-control-sm passenger-type">
                        <option value="adult">Người lớn</option>
                        <option value="child">Trẻ em</option>
                        <option value="infant">Trẻ nhỏ</option>
                        <option value="youth">Em bé</option>
                    </select>
                </div>
                <div class="form-group col-md-3 mb-2">
                    <label class="mb-0">Giới tính</label>
                    <select class="form-control form-control-sm passenger-gender">
                        <option value="">--</option>
                        <option value="male">Nam</option>
                        <option value="female">Nữ</option>
                        <option value="other">Khác</option>
                    </select>
                </div>
                <div class="form-group col-md-2 mb-2 passenger-single-room-wrapper">
                    <label class="mb-0">Phòng đơn</label>
                    <div class="form-check mt-1">
                        <input type="checkbox" class="form-check-input passenger-single-room" value="1">
                        <label class="form-check-label small">Có</label>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4 mb-0">
                    <label class="mb-0">Ngày sinh</label>
                    <input type="date" class="form-control form-control-sm passenger-dob">
                </div>
                <div class="form-group col-md-4 mb-0">
                    <label class="mb-0">Số giấy tờ</label>
                    <input type="text" class="form-control form-control-sm passenger-id-no">
                </div>
            </div>
        </div>
    </template>

    <script>
        (function() {
            const container = document.getElementById('passengerList');
            const template = document.getElementById('passengerRowTemplate');
            const addBtn = document.getElementById('addPassengerBtn');
            const summaryBody = document.getElementById('summaryBody');

            // Giá từ server để dùng tính tạm tính trên giao diện
            const priceAdult = {{ (float) ($departure->price_adult ?? 0) }};
            const priceChild = {{ (float) ($departure->price_child ?? 0) }};
            const priceInfant = {{ (float) ($departure->price_infant ?? 0) }};
            const priceYouth = {{ (float) ($departure->price_youth ?? 0) }};
            const singleRoomPrice = {{ (float) ($departure->single_room_surcharge ?? 0) }};

            if (!container || !template || !addBtn) return;

            let counter = 0;

            function formatCurrency(value) {
                const v = Number(value) || 0;
                return v.toLocaleString('vi-VN') + ' đ';
            }

            function updateSummary() {
                if (!summaryBody) return;

                const rows = container.querySelectorAll('.passenger-row');
                let adultCount = 0;
                let childCount = 0;
                let infantCount = 0;
                let youthCount = 0;
                let singleRooms = 0;

                rows.forEach((row) => {
                    const typeSelect = row.querySelector('.passenger-type');
                    const type = typeSelect ? typeSelect.value : 'adult';
                    const singleCheckbox = row.querySelector('.passenger-single-room');

                    if (type === 'adult') adultCount++;
                    else if (type === 'child') childCount++;
                    else if (type === 'infant') infantCount++;
                    else if (type === 'youth') youthCount++;

                    const isSingle =
                        type === 'adult' &&
                        singleCheckbox &&
                        !singleCheckbox.disabled &&
                        singleCheckbox.checked;
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

            function renameFields() {
                const rows = container.querySelectorAll('.passenger-row');
                rows.forEach((row, index) => {
                    row.querySelector('.passenger-index').textContent = index + 1;

                    row.querySelector('.passenger-full-name').setAttribute('name',
                        `passengers[${index}][full_name]`);
                    row.querySelector('.passenger-type').setAttribute('name',
                        `passengers[${index}][passenger_type]`);
                    row.querySelector('.passenger-gender').setAttribute('name', `passengers[${index}][gender]`);
                    row.querySelector('.passenger-single-room').setAttribute('name',
                        `passengers[${index}][single_room]`);
                    row.querySelector('.passenger-dob').setAttribute('name', `passengers[${index}][dob]`);
                    row.querySelector('.passenger-id-no').setAttribute('name', `passengers[${index}][id_no]`);
                });

                updateSummary();
            }

            function addRow(defaultType = 'adult') {
                const clone = document.importNode(template.content, true);
                const row = clone.querySelector('.passenger-row');
                container.appendChild(clone);

                const newRow = container.querySelectorAll('.passenger-row')[container.querySelectorAll('.passenger-row')
                    .length - 1];
                const typeSelect = newRow.querySelector('.passenger-type');
                if (typeSelect) {
                    typeSelect.value = defaultType;
                }

                function updateSingleRoomVisibility() {
                    const wrapper = newRow.querySelector('.passenger-single-room-wrapper');
                    const checkbox = newRow.querySelector('.passenger-single-room');
                    const type = typeSelect ? typeSelect.value : 'adult';

                    if (!wrapper || !checkbox) return;

                    if (type === 'adult') {
                        wrapper.classList.remove('d-none');
                        checkbox.disabled = false;
                    } else {
                        wrapper.classList.add('d-none');
                        checkbox.checked = false;
                        checkbox.disabled = true;
                    }
                }

                if (typeSelect) {
                    typeSelect.addEventListener('change', function() {
                        updateSingleRoomVisibility();
                        updateSummary();
                    });
                }

                updateSingleRoomVisibility();

                const singleCheckbox = newRow.querySelector('.passenger-single-room');
                if (singleCheckbox) {
                    singleCheckbox.addEventListener('change', updateSummary);
                }

                const removeBtn = newRow.querySelector('.remove-passenger-btn');
                removeBtn.addEventListener('click', function() {
                    newRow.remove();
                    renameFields();
                    updateSummary();
                });

                renameFields();
            }

            addBtn.addEventListener('click', function() {
                addRow('adult');
            });

            // Mặc định thêm 1 hành khách người lớn
            addRow('adult');

            // Cập nhật tạm tính lần đầu
            updateSummary();
        })();
    </script>
@endsection
