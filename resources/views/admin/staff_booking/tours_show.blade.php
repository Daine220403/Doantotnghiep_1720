@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Tour: {{ $tour->title }}</h1>
                <p class="mb-0 text-muted">
                    Xem lịch khởi hành, danh sách khách đã đặt và thao tác đặt/huỷ tour giúp khách.
                </p>
            </div>
            <a href="{{ route('admin.staff-booking.tours') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách tour
            </a>
        </div>

        {{-- Thông tin tổng quan tour --}}
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-2">
                        <h6 class="m-0 font-weight-bold text-primary">Thông tin tour</h6>
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
                        <p class="mb-0"><span class="text-muted">Trạng thái:</span> {{ $tour->status }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                @php
                    $totalDepartures = $tour->departures->count();
                    $totalBookings = 0;
                    $totalPassengers = 0;
                    foreach ($tour->departures as $d) {
                        $totalBookings += $d->bookings->count();
                        foreach ($d->bookings as $b) {
                            $totalPassengers += $b->passengers->count();
                        }
                    }
                @endphp
                <div class="card shadow-sm h-100">
                    <div class="card-header py-2">
                        <h6 class="m-0 font-weight-bold text-primary">Tổng quan booking</h6>
                    </div>
                    <div class="card-body small">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <p class="mb-1 text-muted">Số lịch khởi hành</p>
                                <p class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalDepartures }}</p>
                            </div>
                            <div class="col-md-4 mb-2">
                                <p class="mb-1 text-muted">Tổng số booking</p>
                                <p class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalBookings }}</p>
                            </div>
                            <div class="col-md-4 mb-2">
                                <p class="mb-1 text-muted">Tổng số khách</p>
                                <p class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPassengers }}</p>
                            </div>
                        </div>
                        <p class="mb-0 text-muted small">Nhân viên có thể đặt tour mới cho khách hoặc huỷ các booking không còn hiệu lực.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Danh sách lịch khởi hành & booking --}}
        @foreach ($tour->departures as $departure)
            @php
                $bookings = $departure->bookings;
                $seatLeft = max(($departure->capacity_total ?? 0) - ($departure->capacity_booked ?? 0), 0);
            @endphp
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center py-2">
                    <div>
                        <h6 class="m-0 font-weight-bold text-primary">
                            Lịch khởi hành: {{ \Carbon\Carbon::parse($departure->start_date)->format('d/m/Y') }}
                            - {{ \Carbon\Carbon::parse($departure->end_date)->format('d/m/Y') }}
                        </h6>
                        <div class="small text-muted">
                            Điểm tập trung: {{ $departure->meeting_point ?? $tour->departure_location }}
                        </div>
                    </div>
                    <div class="text-right small">
                        <div>Số chỗ: {{ $departure->capacity_total }}</div>
                        <div>Đã đặt: {{ $departure->capacity_booked }}</div>
                        <div>Còn lại: <strong class="text-danger">{{ $seatLeft }}</strong></div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="small text-muted">
                            Giá người lớn: <strong>{{ number_format($departure->price_adult, 0, ',', '.') }} đ</strong>
                            @if ($departure->price_child)
                                | Trẻ em: <strong>{{ number_format($departure->price_child, 0, ',', '.') }} đ</strong>
                            @endif
                        </div>
                        <a href="{{ route('admin.staff-booking.create', $departure->id) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-plus"></i> Đặt tour cho khách
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0 align-middle">
                            <thead class="bg-light">
                                <tr class="text-center">
                                    <th>#</th>
                                    <th>Mã booking</th>
                                    <th>Khách hàng</th>
                                    <th>Số khách</th>
                                    <th>Ghi chú</th>
                                    <th>Trạng thái thanh toán</th>
                                    <th>Tổng tiền</th>
                                    <th>Đã trả</th>
                                    <th>Còn lại</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bookings as $idx => $booking)
                                    @php
                                        $order = $booking->order;
                                        $pCount = $booking->passengers->count();
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $idx + 1 }}</td>
                                        <td class="text-center">BK{{ $booking->id }}</td>
                                        <td>
                                            @if ($order)
                                                <div class="font-weight-bold small">{{ $order->contact_name }}</div>
                                                <div class="small text-muted">{{ $order->contact_phone }}</div>
                                            @else
                                                <span class="text-muted small">Không có thông tin khách</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $pCount }}</td>
                                        <td class="small">{{ $booking->note ?: '-' }}</td>
                                        <td class="text-center">
                                            @php
                                                $order = $booking->order;
                                                $orderStatus = $order->status ?? 'pending';
                                                $statusBadgeClass = [
                                                    'pending' => 'badge-warning',
                                                    'partial_paid' => 'badge-info',
                                                    'paid' => 'badge-success',
                                                    'cancelled' => 'badge-danger',
                                                ][$orderStatus] ?? 'badge-secondary';
                                                $statusLabel = [
                                                    'pending' => 'Chưa thanh toán',
                                                    'partial_paid' => 'Đã đặt cọc',
                                                    'paid' => 'Đã thanh toán đủ',
                                                    'cancelled' => 'Đã huỷ',
                                                ][$orderStatus] ?? $orderStatus;
                                            @endphp
                                            @if ($order)
                                                <span class="badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                                            @else
                                                <span class="badge badge-secondary">Không có đơn</span>
                                            @endif
                                        </td>
                                        <td class="text-right small">
                                            @if ($order && isset($booking->total_amount))
                                                {{ number_format($booking->total_amount, 0, ',', '.') }} đ
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-right small">
                                            @if ($order && isset($booking->paid_amount))
                                                {{ number_format($booking->paid_amount, 0, ',', '.') }} đ
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-right small">
                                            @if ($order && isset($booking->remaining_amount))
                                                {{ number_format($booking->remaining_amount, 0, ',', '.') }} đ
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $orderStatus = $order->status ?? null;
                                                $isPaidOrder = $orderStatus === 'paid';
                                                $isDepositOrder = $orderStatus === 'partial_paid';
                                            @endphp

                                            <div class="d-flex flex-column align-items-center">
                                                @if (!$isPaidOrder && $booking->status !== 'cancelled')
                                                    <a href="{{ route('admin.staff-booking.edit', $booking->id) }}"
                                                        class="btn btn-sm btn-outline-primary mb-1">
                                                        Xem / Sửa
                                                    </a>
                                                @endif

                                                @if ($booking->status === 'cancelled')
                                                    <span class="text-muted small">Đã huỷ</span>
                                                @elseif($isPaidOrder)
                                                    <span class="text-muted small">Đã thanh toán đủ</span>
                                                @else
                                                    @if ($order)
                                                        <div class="d-flex flex-column align-items-stretch mb-1" style="gap: 4px;">
                                                            @if (!$isDepositOrder)
                                                                <form action="{{ route('admin.staff-booking.deposit-vnpay', $booking->id) }}" method="POST"
                                                                    onsubmit="return confirm('Xác nhận đã thu cọc 30% cho booking này?');">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-sm btn-warning btn-block">
                                                                        Cọc 30%
                                                                    </button>
                                                                </form>
                                                            @endif
                                                            <form action="{{ route('admin.staff-booking.pay-full-vnpay', $booking->id) }}" method="POST"
                                                                onsubmit="return confirm('Xác nhận đã thu đủ tiền cho booking này?');">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-success btn-block">
                                                                    Thanh toán đủ
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @endif

                                                    <form action="{{ route('admin.staff-booking.cancel', $booking->id) }}" method="POST"
                                                        onsubmit="return confirm('Bạn chắc chắn muốn huỷ booking này cho khách?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-block">
                                                            Huỷ booking
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted small">
                                            Chưa có khách nào đặt lịch khởi hành này.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
