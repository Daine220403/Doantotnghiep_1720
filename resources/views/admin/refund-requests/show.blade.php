@php
    $title = 'Chi tiết yêu cầu hoàn tiền - ' . $refund->refund_code;
@endphp

@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">

        <!-- Header -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">{{ $title }}</h1>
                <p class="mb-0 text-muted">Ngày yêu cầu: {{ $refund->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <span class="badge badge-pill {{ $refund->getStatusBadgeClass() }} px-3 py-2">
                {{ $refund->getStatusLabel() }}
            </span>
        </div>

        <!-- Alerts -->
        @if ($refund->status === 'rejected')
            <div class="alert alert-danger">
                <strong>Lý do từ chối:</strong><br>
                {{ $refund->rejection_reason }}
            </div>
        @elseif ($refund->status === 'failed')
            <div class="alert alert-secondary">
                <strong>Lỗi:</strong><br>
                {{ $refund->vnpay_response['error'] ?? 'Lỗi không xác định' }}
            </div>
        @endif

        <div class="row">

            <!-- LEFT -->
            <div class="col-lg-8">

                <!-- Customer -->
                <div class="card shadow mb-4">
                    <div class="card-header font-weight-bold">Thông tin khách hàng</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-2"><strong>Tên:</strong> {{ $refund->user->name }}</div>
                            <div class="col-md-6 mb-2"><strong>Email:</strong> {{ $refund->user->email }}</div>
                            <div class="col-md-6 mb-2"><strong>Điện thoại:</strong> {{ $refund->user->phone ?? 'N/A' }}
                            </div>
                            <div class="col-md-6 mb-2"><strong>Mã đơn:</strong> {{ $refund->order->order_code }}</div>
                        </div>
                    </div>
                </div>

                <!-- Tour -->
                <div class="card shadow mb-4">
                    <div class="card-header font-weight-bold">Thông tin tour</div>
                    <div class="card-body">
                        @php $tour = $refund->booking->departure->tour; @endphp

                        <div class="mb-2"><strong>Tên tour:</strong> {{ $tour->title }}</div>

                        <div class="row">
                            <div class="col-md-6 mb-2"><strong>Điểm đến:</strong> {{ $tour->destination_text }}</div>
                            <div class="col-md-6 mb-2">
                                <strong>Ngày khởi hành:</strong>
                                {{ $refund->booking->departure->start_date ? \Carbon\Carbon::parse($refund->booking->departure->start_date)->format('d/m/Y') : 'N/A' }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Số hành khách:</strong> {{ $refund->booking->passengers->count() }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Số tiền tour:</strong>
                                {{ number_format($refund->order->total_amount, 0, ',', '.') }} đ
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Refund -->
                <div class="card shadow mb-4">
                    <div class="card-header font-weight-bold">Thông tin hoàn tiền</div>
                    <div class="card-body">

                        <div class="d-flex justify-content-between border-bottom pb-3 mb-3">
                            <span>Số tiền cần hoàn</span>
                            <strong class="text-success h5 mb-0">
                                {{ number_format($refund->refund_amount, 0, ',', '.') }} đ
                            </strong>
                        </div>

                        <div>
                            <strong>Phương thức:</strong>
                            @if ($refund->refund_method === 'vnpay')
                                <span class="badge badge-primary">VNPay</span>
                            @elseif ($refund->refund_method === 'wallet')
                                <span class="badge badge-info">Ví tiền</span>
                            @endif
                        </div>

                    </div>
                </div>

                <!-- Passengers -->
                @if ($refund->booking->passengers->count())
                    <div class="card shadow mb-4">
                        <div class="card-header font-weight-bold">Danh sách hành khách</div>
                        <div class="card-body table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tên</th>
                                        <th>Loại</th>
                                        <th>Giới tính</th>
                                        <th>Ngày sinh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($refund->booking->passengers as $p)
                                        <tr>
                                            <td>{{ $p->full_name }}</td>
                                            <td><span class="badge badge-secondary">{{ $p->passenger_type }}</span></td>
                                            <td>{{ $p->gender ?? '-' }}</td>
                                            <td>{{ $p->dob ? \Carbon\Carbon::parse($p->dob)->format('d/m/Y') : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Timeline -->
                @if ($refund->status !== 'pending')
                    <div class="card shadow mb-4">
                        <div class="card-header font-weight-bold">Lịch sử xử lý</div>
                        <div class="card-body">

                            @if ($refund->approved_at)
                                <p>✔ Đã duyệt bởi <strong>{{ $refund->approvedBy->name }}</strong> lúc
                                    {{ $refund->approved_at->format('d/m/Y H:i') }}</p>
                            @endif

                            @if ($refund->refunded_at)
                                <p>✔ Đã hoàn tiền lúc {{ $refund->refunded_at->format('d/m/Y H:i') }}</p>
                            @endif

                            @if ($refund->rejected_at)
                                <p>✖ Bị từ chối lúc {{ $refund->rejected_at->format('d/m/Y H:i') }}</p>
                            @endif

                        </div>
                    </div>
                @endif

            </div>

            <!-- RIGHT -->
            <div class="col-lg-4">

                <!-- Status -->
                <div class="card shadow mb-4">
                    <div class="card-header font-weight-bold">Trạng thái</div>
                    <div class="card-body">

                        <p class="{{ $refund->status === 'pending' ? 'text-warning' : 'text-muted' }}">● Chờ duyệt</p>
                        <p class="{{ $refund->status === 'approved' ? 'text-primary' : 'text-muted' }}">● Đã duyệt</p>
                        <p class="{{ $refund->status === 'refunded' ? 'text-success' : 'text-muted' }}">● Đã hoàn tiền</p>
                        <p class="{{ $refund->status === 'rejected' ? 'text-danger' : 'text-muted' }}">● Từ chối</p>

                    </div>
                </div>

                <!-- Actions -->
                @if ($refund->canApprove())
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.refund-requests.approve', $refund->id) }}">
                                @csrf
                                <textarea name="note" class="form-control mb-2" placeholder="Ghi chú..."></textarea>
                                <button class="btn btn-primary btn-block">Duyệt</button>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.refund-requests.reject', $refund->id) }}"
                                onsubmit="return confirm('Xác nhận từ chối?')">
                                @csrf
                                <textarea name="reason" required class="form-control mb-2" placeholder="Lý do từ chối"></textarea>
                                <button class="btn btn-danger btn-block">Từ chối</button>
                            </form>
                        </div>
                    </div>
                @elseif ($refund->status === 'approved')
                    <div class="card shadow mb-4">
                        <div class="card-body text-center">
                            <p>Đã duyệt, tiến hành hoàn tiền</p>
                            <form method="POST" action="{{ route('admin.refund-requests.test-process', $refund->id) }}">
                                @csrf
                                <button class="btn btn-success btn-block">Xác nhận hoàn tiền</button>
                            </form>
                        </div>
                    </div>
                @elseif ($refund->status === 'refunded')
                    <div class="alert alert-success text-center">Đã hoàn tiền</div>
                @elseif ($refund->status === 'rejected')
                    <div class="alert alert-danger text-center">Đã bị từ chối</div>
                @endif

                <a href="{{ route('admin.refund-requests.index') }}" class="btn btn-secondary btn-block">
                    ← Quay lại
                </a>

            </div>

        </div>

    </div>
@endsection
