@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Báo cáo tổng kết hoạt động tour</h1>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('guide.departures.report.store', $departure->id) }}">
            @csrf

            {{-- Thông tin chung --}}
            <div class="card mb-4">
                <div class="card-header">Thông tin chung</div>
                <div class="card-body row">
                    <div class="col-md-6">
                        <p><strong>Mã tour:</strong> {{ $departure->tour->code ?? '-' }}</p>
                        <p><strong>Tên tour:</strong> {{ $departure->tour->title ?? '-' }}</p>
                        <p><strong>Thời gian:</strong> {{ $departure->start_date }} - {{ $departure->end_date }}</p>
                        <p><strong>Điểm tập trung:</strong> {{ $departure->meeting_point }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Trạng thái lịch khởi hành:</strong> {{ $departure->status }}</p>
                        <p><strong>Tổng số booking:</strong> {{ $totalBookings }}</p>
                        <p><strong>Tổng số khách:</strong> {{ $totalPassengers }}</p>
                        <p>
                            <strong>Cơ cấu khách:</strong>
                            Người lớn: {{ $byType['adult'] ?? 0 }},
                            Trẻ em: {{ $byType['child'] ?? 0 }},
                            Trẻ nhỏ: {{ $byType['infant'] ?? 0 }},
                            Em bé: {{ $byType['youth'] ?? 0 }}
                        </p>
                        <p>
                            <strong>Phòng đơn:</strong>
                            {{ $singleRoomCount }} khách (phụ thu:
                            {{ number_format($singleRoomSurchargeTotal, 0, ',', '.') }} đ)
                        </p>
                    </div>
                </div>
            </div>

            {{-- 2. Báo cáo thực tế của HDV --}}
            <div class="card mb-4">
                <div class="card-header">Báo cáo thực tế của HDV</div>
                <div class="card-body row">
                    <div class="col-md-12 mb-3">
                        <h6><strong>Tóm tắt chương trình / Hoạt động chính</strong></h6>
                        @php
                            $itineraries = $departure->tour->itineraries->sortBy('day_no');
                        @endphp

                        @if ($itineraries->isEmpty())
                            <p class="mb-0">Chưa có chương trình chi tiết cho tour này.</p>
                        @else
                            <div class="table-responsive mb-0">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 80px;">Ngày</th>
                                            <th>Tiêu đề</th>
                                            <th>Nội dung chính</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($itineraries as $item)
                                            <tr>
                                                <td>Ngày {{ $item->day_no }}</td>
                                                <td>{{ $item->title }}</td>
                                                <td>{!! nl2br(e($item->content)) !!}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <h6><strong>Đánh giá chung về đoàn</strong></h6>
                        <textarea name="general_evaluation" class="form-control" rows="4" placeholder="Nhận xét chung về đoàn">{{ old('general_evaluation', $report->general_evaluation) }}</textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6><strong>Ghi chú lịch trình thực tế</strong></h6>
                        <textarea name="itinerary_notes" class="form-control" rows="4"
                            placeholder="Lịch trình thực tế, thay đổi so với chương trình tiêu chuẩn nếu có">{{ old('itinerary_notes', $report->itinerary_notes) }}</textarea>
                    </div>


                    <div class="col-md-6 mb-3">
                        <h6><strong>Phản hồi khách hàng</strong></h6>
                        <textarea name="customer_feedback" class="form-control" rows="4"
                            placeholder="Ý kiến, đánh giá, khiếu nại/khen ngợi từ khách hàng">{{ old('customer_feedback', $report->customer_feedback) }}</textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6><strong>Đề xuất / Kiến nghị của HDV</strong></h6>
                        <textarea name="guide_suggestion" class="form-control" rows="4"
                            placeholder="Đề xuất cải thiện chương trình, dịch vụ, quy trình...">{{ old('guide_suggestion', $report->guide_suggestion) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- 3. Tổng hợp chi phí --}}
            <div class="card mb-4">
                <div class="card-header">Tổng hợp chi phí</div>
                <div class="card-body">
                    @php
                        $services = $departure->services;
                    @endphp

                    @if ($services->isEmpty())
                        <div class="alert alert-info mb-0">Chưa có dịch vụ/chi phí nào được ghi nhận cho lịch khởi hành này.
                        </div>
                    @else
                        <div class="table-responsive mb-3">
                            <h6><strong>Bảng dịch vụ</strong></h6>
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Ngày sử dụng</th>
                                        <th>Dịch vụ</th>
                                        <th>Đối tác</th>
                                        <th>Số lượng</th>
                                        <th>Đơn giá</th>
                                        <th>Thành tiền</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($services as $service)
                                        <tr>
                                            <td>{{ $service->service_date }}</td>
                                            <td>{{ $service->partnerService->name ?? '-' }}</td>
                                            <td>{{ $service->partnerService->partner->name ?? '-' }}</td>
                                            <td>{{ $service->qty }}</td>
                                            <td>{{ number_format($service->unit_price, 0, ',', '.') }} đ</td>
                                            <td>{{ number_format($service->total_price, 0, ',', '.') }} đ</td>
                                            <td>{{ $service->note }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6><strong>Sự cố / Phát sinh & Chi phí liên quan</strong></h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addIncidentRow()">+
                                    Thêm
                                    dòng</button>
                                @php
                                    $incidentRows = [];
                                    if (old('incidents_rows')) {
                                        $incidentRows = old('incidents_rows');
                                    } else {
                                        if (!empty($report->incidents)) {
                                            $decoded = json_decode($report->incidents, true);
                                            if (is_array($decoded)) {
                                                $incidentRows = $decoded;
                                            }
                                        }
                                    }
                                    if (empty($incidentRows)) {
                                        $incidentRows = [['description' => '', 'cost' => '']];
                                    }
                                @endphp

                                <div class="table-responsive mb-2">
                                    <table class="table table-sm table-bordered" id="incident-rows-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 55%;">Nội dung sự cố / phát sinh</th>
                                                <th style="width: 25%;">Chi phí (đ)</th>
                                                <th style="width: 20%;">Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($incidentRows as $idx => $row)
                                                <tr>
                                                    <td>
                                                        <input type="text"
                                                            name="incidents_rows[{{ $idx }}][description]"
                                                            class="form-control" value="{{ $row['description'] ?? '' }}"
                                                            placeholder="Mô tả sự cố, phát sinh, cách xử lý...">
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                            name="incidents_rows[{{ $idx }}][cost]"
                                                            class="form-control" min="0" step="1000"
                                                            value="{{ $row['cost'] ?? '' }}" placeholder="0">
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                            onclick="removeIncidentRow(this)">Xóa</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6 mt-5">
                                <p class="mb-1"><strong>Tổng chi phí phát sinh:</strong>
                                    <span id="extra-cost-total-display">{{ number_format($extraCostTotal, 0, ',', '.') }}</span> đ</p>
                                <p class="mb-1"><strong>Tổng chi phí dịch vụ:</strong>
                                    <span id="service-cost-base" data-service-cost="{{ $serviceCostTotal }}">{{ number_format($serviceCostTotal, 0, ',', '.') }}</span> đ</p>
                                <p class="mb-0"><strong>Tổng chi phí (dịch vụ + phát sinh khác):</strong>
                                    <span id="total-cost-display">{{ number_format($totalCost, 0, ',', '.') }}</span> đ</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            {{-- 4. Tổng kết cuối --}}
            <div class="card mb-4">
                <div class="card-header">Tổng kết cuối</div>
                <div class="card-body row">
                    <div class="col-md-12 mb-3">
                        <h6><strong>Tổng kết của HDV</strong></h6>
                        <textarea name="summary" class="form-control" rows="4"
                            placeholder="Tổng kết chung về tour, rút kinh nghiệm cho lần sau">{{ old('summary', $report->summary ?? $report->general_evaluation) }}</textarea>
                    </div>

                    @if (!empty($report->manager_note))
                        <div class="col-md-6 mb-3">
                            <h6><strong>Ghi chú của quản lý</strong></h6>
                            <p class="mb-0">{!! nl2br(e($report->manager_note)) !!}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mb-4">
                <button type="submit" class="btn btn-primary">Gửi / Lưu báo cáo</button>
                <a href="{{ route('guide.tours.index') }}" class="btn btn-secondary ml-2">Quay lại danh sách tour</a>
            </div>

        </form>

        <script>
            function addIncidentRow() {
                const table = document.getElementById('incident-rows-table');
                if (!table) return;

                const tbody = table.querySelector('tbody');
                const currentRows = tbody.querySelectorAll('tr').length;
                const idx = currentRows;

                const tr = document.createElement('tr');
                tr.innerHTML = `
				<td>
					<input type="text" name="incidents_rows[${idx}][description]" class="form-control" placeholder="Mô tả sự cố, phát sinh, cách xử lý...">
				</td>
				<td>
					<input type="number" name="incidents_rows[${idx}][cost]" class="form-control" min="0" step="1000" placeholder="0">
				</td>
				<td class="text-center align-middle">
					<button type="button" class="btn btn-sm btn-danger" onclick="removeIncidentRow(this)">Xóa</button>
				</td>
			`;

                tbody.appendChild(tr);

                    // Gán sự kiện và tính lại tổng sau khi thêm dòng
                    const costInput = tr.querySelector('input[name$="[cost]"]');
                    if (costInput) {
                        costInput.addEventListener('input', recalcIncidentTotal);
                    }
                    recalcIncidentTotal();
            }

            function removeIncidentRow(button) {
                const row = button.closest('tr');
                const tbody = row && row.parentElement;
                if (!row || !tbody) return;

                if (tbody.querySelectorAll('tr').length <= 1) {
                    row.querySelectorAll('input').forEach(input => input.value = '');
                    recalcIncidentTotal();
                    return;
                }

                row.remove();
                recalcIncidentTotal();
            }

            function recalcIncidentTotal() {
                const table = document.getElementById('incident-rows-table');
                const displayEl = document.getElementById('extra-cost-total-display');
                const totalDisplayEl = document.getElementById('total-cost-display');
                const serviceBaseEl = document.getElementById('service-cost-base');
                if (!table || !displayEl) return;

                const costInputs = table.querySelectorAll('tbody input[name$="[cost]"]');
                let total = 0;
                costInputs.forEach(input => {
                    const val = parseFloat(input.value.replace(/,/g, ''));
                    if (!isNaN(val)) {
                        total += val;
                    }
                });

                displayEl.textContent = total.toLocaleString('vi-VN');

                // Cập nhật Tổng chi phí (dịch vụ + phát sinh khác)
                if (totalDisplayEl && serviceBaseEl) {
                    const serviceCost = parseFloat(serviceBaseEl.getAttribute('data-service-cost')) || 0;
                    const grandTotal = serviceCost + total;
                    totalDisplayEl.textContent = grandTotal.toLocaleString('vi-VN');
                }
            }

            function bindIncidentCostListeners() {
                const table = document.getElementById('incident-rows-table');
                if (!table) return;

                const costInputs = table.querySelectorAll('tbody input[name$="[cost]"]');
                costInputs.forEach(input => {
                    input.addEventListener('input', recalcIncidentTotal);
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                bindIncidentCostListeners();
                recalcIncidentTotal();
            });
        </script>
    </div>
@endsection
