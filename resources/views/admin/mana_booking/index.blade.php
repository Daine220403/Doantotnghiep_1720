@extends('admin.layout.app')
{{-- <script src="{{ asset('ckeditor/ckeditor.js') }}"></script> --}}
<style>
    #dataTable tbody td {
        vertical-align: middle !important;
    }

    #dataTable tbody td div {
        margin-bottom: 0 !important;
    }
</style>
@section('content')
    <div class="container-fluid">
        {{-- Page Heading --}}
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Quản lý booking</h1>
                <p class="mb-0 text-muted">
                    Danh sách các booking tour của khách hàng. Bạn có thể theo dõi và cập nhật trạng thái booking.
                </p>
            </div>

            <form action="" method="GET" class="d-flex align-items-center">
                <label for="status" class="mr-2 mb-0 small text-muted">Lọc theo trạng thái:</label>
                <select name="status" id="status" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">Tất cả</option>
                    <option value="pending" {{ (isset($status) && $status == 'pending') ? 'selected' : '' }}>Chờ xử lý</option>
                    <option value="confirmed" {{ (isset($status) && $status == 'confirmed') ? 'selected' : '' }}>Đã xác nhận</option>
                    <option value="paid" {{ (isset($status) && $status == 'paid') ? 'selected' : '' }}>Đã thanh toán</option>
                    <option value="cancelled" {{ (isset($status) && $status == 'cancelled') ? 'selected' : '' }}>Đã hủy</option>
                    <option value="completed" {{ (isset($status) && $status == 'completed') ? 'selected' : '' }}>Hoàn tất</option>
                </select>
            </form>
        </div>

        {{-- Flash message --}}
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

        {{-- Card --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm text-center align-middle" id="dataTable"
                        width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Mã đơn</th>
                                <th>Tour</th>
                                <th>Lịch khởi hành</th>
                                <th>Khách hàng</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái booking</th>
                                <th>Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                $index = 1;
                            @endphp

                            @forelse ($bookings as $booking)
                                @php
                                    $order = $booking->order;
                                    $departure = $booking->departure;
                                    $tour = $departure ? $departure->tour : null;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $index++ }}</td>

                                    <td>
                                        <div class="font-weight-bold text-gray-800">{{ $order->order_code ?? 'N/A' }}</div>
                                    </td>

                                    <td class="text-left">
                                        @if ($tour)
                                            <div class="font-weight-bold text-gray-800">{{ $tour->title }}</div>
                                        @else
                                            <span class="text-muted small">Không tìm thấy tour</span>
                                        @endif
                                    </td>

                                    <td class="text-left">
                                        @if ($departure)
                                            <div class="small">
                                                {{ \Carbon\Carbon::parse($departure->start_date)->format('d/m/Y') }}
                                                -
                                                {{ \Carbon\Carbon::parse($departure->end_date)->format('d/m/Y') }}
                                            </div>
                                        @else
                                            <span class="text-muted small">Không có lịch khởi hành</span>
                                        @endif
                                    </td>

                                    <td class="text-left">
                                        @if ($order)
                                            <div class="font-weight-bold text-gray-800">{{ $order->contact_name }}</div>
                                            <div class="small text-muted">{{ $order->contact_phone }}</div>
                                        @else
                                            <span class="text-muted small">Không có thông tin khách</span>
                                        @endif
                                    </td>

                                    <td class="text-right">
                                        @if ($order)
                                            {{ number_format($order->total_amount, 0, ',', '.') }} đ
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @php
                                            $statusLabel = [
                                                'pending' => 'Chờ xử lý',
                                                'confirmed' => 'Đã xác nhận',
                                                'paid' => 'Đã thanh toán',
                                                'cancelled' => 'Đã hủy',
                                                'completed' => 'Hoàn tất',
                                            ];

                                            $statusClass = [
                                                'pending' => 'badge-secondary',
                                                'confirmed' => 'badge-info',
                                                'paid' => 'badge-primary',
                                                'cancelled' => 'badge-danger',
                                                'completed' => 'badge-success',
                                            ];

                                            $currentStatus = $booking->status;
                                        @endphp

                                        <span class="badge {{ $statusClass[$currentStatus] ?? 'badge-light' }} mb-2">
                                            {{ $statusLabel[$currentStatus] ?? $currentStatus }}
                                        </span>

                                        <form action="{{ route('admin.mana-booking.update-status', $booking->id) }}" method="POST" class="d-inline-block">
                                            @csrf
                                            @method('PUT')
                                            <div class="input-group input-group-sm">
                                                <select name="status" class="form-control form-control-sm">
                                                    <option value="pending" {{ $currentStatus == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                                                    <option value="confirmed" {{ $currentStatus == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                                                    <option value="paid" {{ $currentStatus == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                                                    <option value="cancelled" {{ $currentStatus == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                                                    <option value="completed" {{ $currentStatus == 'completed' ? 'selected' : '' }}>Hoàn tất</option>
                                                </select>
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-primary btn-sm" type="submit">Lưu</button>
                                                </div>
                                            </div>
                                        </form>
                                    </td>

                                    <td class="text-center">
                                        {{ $booking->created_at ? $booking->created_at->format('d/m/Y H:i') : '' }}
                                    </td>

                                    <td class="text-center">
                                        <a href="{{ route('admin.mana-booking.show', $booking->id) }}" class="btn btn-sm btn-info" title="Xem chi tiết booking">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Chưa có booking nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection
