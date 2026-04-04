@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Dịch vụ đối tác cho lịch khởi hành</h1>
                <p class="mb-0 text-muted">
                    Tour: <strong>{{ $departure->tour->title }}</strong> | Lịch: {{ $departure->start_date }} - {{ $departure->end_date }}
                </p>
            </div>

            <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-5">
                <div class="card mb-4">
                    <div class="card-header font-weight-bold">Thêm dịch vụ đối tác</div>
                    <div class="card-body">
                        @if ($departure->status === 'confirmed')
                            <form action="{{ route('admin.departures.services.store', $departure->id) }}" method="POST">
                                @csrf

                            <div class="form-group">
                                <label>Công ty dịch vụ</label>
                                <input type="text" id="partner_search" class="form-control mb-2" placeholder="Tìm kiếm công ty dịch vụ...">
                                <select class="form-control" id="partner_select">
                                    <option value="">-- Chọn công ty dịch vụ --</option>
                                    @foreach ($partners as $partner)
                                        <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Dịch vụ của công ty</label>
                                <select name="partner_service_id" class="form-control" id="partner_service_select" disabled>
                                    <option value="">-- Chọn dịch vụ --</option>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}" data-partner-id="{{ $service->partner_id }}" data-unit-price="{{ $service->unit_price ?? 0 }}">
                                            {{ $service->name }} ({{ $service->service_type }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('partner_service_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Ngày sử dụng dịch vụ (từ - đến)</label>
                                <div class="d-flex">
                                    <input type="date" name="service_start_date" class="form-control mr-2" value="{{ old('service_start_date', $departure->start_date) }}">
                                    <input type="date" name="service_end_date" class="form-control" value="{{ old('service_end_date', $departure->end_date) }}">
                                </div>
                                @error('service_start_date')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                @error('service_end_date')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Số lượng (phòng/bữa/chỗ,...)</label>
                                <input type="number" name="qty" class="form-control" value="{{ old('qty', 1) }}" min="1">
                                @error('qty')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Đơn giá</label>
                                <input type="number" step="0.01" name="unit_price" id="unit_price_input" class="form-control" value="{{ old('unit_price', 0) }}" min="0" readonly>
                                @error('unit_price')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Trạng thái ẩn, luôn đặt là pending --}}
                            <input type="hidden" name="status" value="pending">

                            <div class="form-group">
                                <label>Ghi chú</label>
                                <textarea name="note" rows="3" class="form-control">{{ old('note') }}</textarea>
                                @error('note')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                                <button type="submit" class="btn btn-primary btn-sm">Thêm dịch vụ</button>
                            </form>
                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    var partnerSelect = document.getElementById('partner_select');
                                    var partnerSearch = document.getElementById('partner_search');
                                    var serviceSelect = document.getElementById('partner_service_select');
                                    var unitInput = document.getElementById('unit_price_input');

                                    if (!partnerSelect || !serviceSelect || !unitInput) return;

                                    function filterServicesByPartner() {
                                        var partnerId = partnerSelect.value;
                                        var hasVisible = false;

                                        Array.prototype.forEach.call(serviceSelect.options, function (opt) {
                                            if (!opt.value) {
                                                // luôn hiển thị option placeholder
                                                opt.style.display = '';
                                                return;
                                            }

                                            var optPartnerId = opt.getAttribute('data-partner-id');
                                            if (partnerId && optPartnerId === partnerId) {
                                                opt.style.display = '';
                                                hasVisible = true;
                                            } else {
                                                opt.style.display = 'none';
                                            }
                                        });

                                        serviceSelect.disabled = !partnerId || !hasVisible;
                                        serviceSelect.value = '';
                                        unitInput.value = 0;
                                    }

                                    function updateUnitPrice() {
                                        var option = serviceSelect.options[serviceSelect.selectedIndex];
                                        if (!option) return;

                                        var price = option.getAttribute('data-unit-price');
                                        if (price !== null && price !== '') {
                                            unitInput.value = price;
                                        }
                                    }

                                    function filterPartners() {
                                        var term = (partnerSearch.value || '').toLowerCase();
                                        Array.prototype.forEach.call(partnerSelect.options, function (opt, index) {
                                            if (index === 0) {
                                                // placeholder luôn hiển thị
                                                opt.style.display = '';
                                                return;
                                            }
                                            var text = (opt.text || '').toLowerCase();
                                            opt.style.display = text.indexOf(term) !== -1 ? '' : 'none';
                                        });
                                    }

                                    partnerSelect.addEventListener('change', filterServicesByPartner);
                                    serviceSelect.addEventListener('change', updateUnitPrice);
                                    if (partnerSearch) {
                                        partnerSearch.addEventListener('input', filterPartners);
                                    }

                                    // Khởi tạo: ẩn tất cả dịch vụ vì chưa chọn công ty
                                    filterServicesByPartner();
                                });
                            </script>
                        @else
                            <p class="text-muted mb-0">
                                Chỉ có thể thêm/chỉnh sửa dịch vụ khi lịch khởi hành đã được <strong>chốt đoàn</strong>.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card mb-4">
                    <div class="card-header font-weight-bold">Danh sách dịch vụ đã thuê</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm text-center align-middle" width="100%" cellspacing="0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Dịch vụ</th>
                                        <th>Ngày sử dụng</th>
                                        <th>SL</th>
                                        <th>Đơn giá</th>
                                        <th>Thành tiền</th>
                                        <th>Trạng thái</th>
                                        <th style="width: 80px;">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $index = 1; @endphp
                                    @forelse ($departure->services as $row)
                                        <tr>
                                            <td>{{ $index++ }}</td>
                                            <td class="text-left">
                                                <div class="font-weight-bold">{{ $row->partnerService->name ?? 'N/A' }}</div>
                                                <div class="small text-muted">Đối tác: {{ $row->partnerService->partner->name ?? 'N/A' }}</div>
                                            </td>
                                            <td>
                                                @if ($row->service_start_date || $row->service_end_date)
                                                    {{ $row->service_start_date }}
                                                    @if ($row->service_end_date)
                                                        - {{ $row->service_end_date }}
                                                    @endif
                                                @else
                                                    {{ $row->service_date }}
                                                @endif
                                            </td>
                                            <td>{{ $row->qty }}</td>
                                            <td class="text-right">{{ number_format($row->unit_price, 0, ',', '.') }}</td>
                                            <td class="text-right">{{ number_format($row->total_price, 0, ',', '.') }}</td>
                                            <td>
                                                @switch($row->status)
                                                    @case('pending')
                                                        <span class="badge badge-secondary">Chờ</span>
                                                        @break
                                                    @case('confirmed')
                                                        <span class="badge badge-info">Đã xác nhận</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge badge-success">Hoàn tất</span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="badge badge-danger">Hủy</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>
                                                <form action="{{ route('admin.departures.services.destroy', [$departure->id, $row->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa dịch vụ này?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">Chưa có dịch vụ nào.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
