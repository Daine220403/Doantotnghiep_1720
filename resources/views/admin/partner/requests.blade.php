@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Yêu cầu dịch vụ cần xác nhận</h1>
                <p class="mb-0 text-muted">
                    Danh sách các yêu cầu dịch vụ cho các lịch khởi hành liên quan đến đối tác <strong>{{ $partner->name }}</strong>.
                </p>
            </div>
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

        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Danh sách yêu cầu dịch vụ</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm text-center align-middle" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Tour</th>
                                <th>Dịch vụ</th>
                                <th>Ngày bắt đầu</th>
                                <th>Ngày kết thúc</th>
                                <th>Số lượng</th>
                                <th>Đơn giá (VNĐ)</th>
                                <th>Thành tiền (VNĐ)</th>
                                <th>Trạng thái</th>
                                <th style="width: 200px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $index = 1; @endphp
                            @forelse ($requests as $item)
                                <tr>
                                    <td>{{ $index++ }}</td>
                                    <td class="text-left">
                                        {{ optional($item->departure->tour)->title ?? 'N/A' }}<br>
                                        <small class="text-muted">Mã lịch: #{{ $item->departure_id }}</small>
                                    </td>
                                    <td class="text-left">
                                        {{ optional($item->partnerService)->name }}
                                    </td>
                                    <td>
                                        {{ $item->service_start_date ?? $item->service_date ?? 'N/A' }}
                                    </td>
                                    <td>
                                        {{ $item->service_end_date ?? 'N/A' }}
                                    </td>
                                    <td>{{ $item->qty }}</td>
                                    <td class="text-right">{{ number_format($item->unit_price ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($item->total_price ?? 0, 0, ',', '.') }}</td>
                                    <td>
                                        @if ($item->cancel_requested && $item->status === 'confirmed')
                                            <span class="badge badge-warning">Đang chờ hủy</span>
                                        @else
                                            @switch($item->status)
                                                @case('pending')
                                                    <span class="badge badge-warning">Chờ xác nhận</span>
                                                    @break
                                                @case('confirmed')
                                                    <span class="badge badge-success">Đã xác nhận</span><br>
                                                    @if ($item->confirmed_at)
                                                        <small class="text-muted">{{ $item->confirmed_at }}</small>
                                                    @endif
                                                    @break
                                                @case('completed')
                                                    <span class="badge badge-primary">Đã hoàn thành</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge badge-secondary">Đã hủy</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-light">N/A</span>
                                            @endswitch
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item->status === 'pending')
                                            <form action="{{ route('admin.partner.requests.confirm', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xác nhận yêu cầu dịch vụ này?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success mb-1">Xác nhận</button>
                                            </form>
                                            <form action="{{ route('admin.partner.requests.reject', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn từ chối yêu cầu dịch vụ này?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger mb-1">Từ chối</button>
                                            </form>
                                        @elseif ($item->status === 'confirmed' && $item->cancel_requested)
                                            <form action="{{ route('admin.partner.requests.cancel-approve', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn chấp nhận hủy dịch vụ này?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning mb-1">Đồng ý hủy</button>
                                            </form>
                                            <form action="{{ route('admin.partner.requests.cancel-reject', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn từ chối yêu cầu hủy dịch vụ này?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-secondary mb-1">Từ chối hủy</button>
                                            </form>
                                        @else
                                            <span class="text-muted small">Không có hành động</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted">Hiện không có yêu cầu dịch vụ nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
