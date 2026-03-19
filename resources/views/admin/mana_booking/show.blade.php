@extends('admin.layout.app')

@section('content')
    @php
        $order = $booking->order;
        $departure = $booking->departure;
        $tour = $departure ? $departure->tour : null;
        $passengers = $booking->passengers;

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

    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Chi tiết booking #{{ $booking->id }}</h1>
                <p class="mb-0 text-muted">
                    Xem đầy đủ thông tin booking, tour, lịch khởi hành và khách hàng.
                </p>
            </div>

            <a href="{{ route('admin.mana-booking.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>

        {{-- Thông tin tổng quan --}}
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-2">
                        <h6 class="m-0 font-weight-bold text-primary">Thông tin booking</h6>
                    </div>
                    <div class="card-body small">
                        <p class="mb-2">
                            <span class="text-muted">Mã booking:</span>
                            <span class="font-weight-bold">#{{ $booking->id }}</span>
                        </p>
                        @if ($order)
                            <p class="mb-2">
                                <span class="text-muted">Mã đơn:</span>
                                <span class="font-weight-bold">{{ $order->order_code }}</span>
                            </p>
                        @endif
                        <p class="mb-2">
                            <span class="text-muted">Trạng thái booking:</span>
                            <span class="badge {{ $statusClass[$currentStatus] ?? 'badge-light' }}">
                                {{ $statusLabel[$currentStatus] ?? $currentStatus }}
                            </span>
                        </p>
                        <p class="mb-2">
                            <span class="text-muted">Ngày tạo:</span>
                            <span>{{ $booking->created_at ? $booking->created_at->format('d/m/Y H:i') : '-' }}</span>
                        </p>
                        <p class="mb-0">
                            <span class="text-muted">Cập nhật lần cuối:</span>
                            <span>{{ $booking->updated_at ? $booking->updated_at->format('d/m/Y H:i') : '-' }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-2">
                        <h6 class="m-0 font-weight-bold text-primary">Thông tin đơn hàng</h6>
                    </div>
                    <div class="card-body small">
                        @if ($order)
                            <p class="mb-2">
                                <span class="text-muted">Tên liên hệ:</span>
                                <span class="font-weight-bold">{{ $order->contact_name }}</span>
                            </p>
                            <p class="mb-2">
                                <span class="text-muted">Số điện thoại:</span>
                                <span>{{ $order->contact_phone }}</span>
                            </p>
                            <p class="mb-2">
                                <span class="text-muted">Email:</span>
                                <span>{{ $order->contact_email }}</span>
                            </p>
                            <p class="mb-2">
                                <span class="text-muted">Tổng tiền:</span>
                                <span class="font-weight-bold text-success">
                                    {{ number_format($order->total_amount, 0, ',', '.') }} đ
                                </span>
                            </p>
                            <p class="mb-0">
                                <span class="text-muted">Trạng thái đơn:</span>
                                <span class="badge badge-info">{{ $statusLabel[$currentStatus] ?? $currentStatus }}</span>
                            </p>
                        @else
                            <p class="mb-0 text-muted">Không có thông tin đơn hàng.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-2">
                        <h6 class="m-0 font-weight-bold text-primary">Ghi chú</h6>
                    </div>
                    <div class="card-body small">
                        @if ($booking->note)
                            <p class="mb-0">{{ $booking->note }}</p>
                        @else
                            <p class="mb-0 text-muted">Không có ghi chú cho booking này.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Thông tin tour & lịch khởi hành --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-2">
                        <h6 class="m-0 font-weight-bold text-primary">Thông tin tour</h6>
                    </div>
                    <div class="card-body small">
                        @if ($tour)
                            <p class="mb-2">
                                <span class="text-muted">Tên tour:</span>
                                <span class="font-weight-bold">{{ $tour->title }}</span>
                            </p>
                            <p class="mb-2">
                                <span class="text-muted">Mã tour:</span>
                                <span>{{ $tour->code }}</span>
                            </p>
                            <p class="mb-2">
                                <span class="text-muted">Điểm đi:</span>
                                <span>{{ $tour->departure_location }}</span>
                            </p>
                            <p class="mb-2">
                                <span class="text-muted">Điểm đến:</span>
                                <span>{{ $tour->destination_text }}</span>
                            </p>
                            <p class="mb-0">
                                <span class="text-muted">Thời gian:</span>
                                <span>{{ $tour->duration_days }}N{{ $tour->duration_nights }}Đ</span>
                            </p>
                        @else
                            <p class="mb-0 text-muted">Không tìm thấy thông tin tour.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-2">
                        <h6 class="m-0 font-weight-bold text-primary">Lịch khởi hành</h6>
                    </div>
                    <div class="card-body small">
                        @if ($departure)
                            <p class="mb-2">
                                <span class="text-muted">Ngày khởi hành:</span>
                                <span>{{ \Carbon\Carbon::parse($departure->start_date)->format('d/m/Y') }}</span>
                            </p>
                            <p class="mb-2">
                                <span class="text-muted">Ngày kết thúc:</span>
                                <span>{{ \Carbon\Carbon::parse($departure->end_date)->format('d/m/Y') }}</span>
                            </p>
                            <p class="mb-2">
                                <span class="text-muted">Điểm tập trung:</span>
                                <span>{{ $departure->meeting_point }}</span>
                            </p>
                            <p class="mb-2">
                                <span class="text-muted">Số chỗ:</span>
                                <span>{{ $departure->capacity_booked }} / {{ $departure->capacity_total }}</span>
                            </p>
                            <p class="mb-2">
                                <span class="text-muted">Số lượng người lớn: <span class="font-weight-bold">{{ $passengers->where('passenger_type', 'adult')->count() }}</span></span>
                                <span class="font-weight-bold">/{{ number_format($departure->price_adult, 0, ',', '.') }} đ</span>
                            </p>
                            <p class="mb-0">
                                <span class="text-muted">Số lượng trẻ em: <span class="font-weight-bold">{{ $passengers->where('passenger_type', 'child')->count() }}</span></span>
                                <span class="font-weight-bold">/{{ number_format($departure->price_child, 0, ',', '.') }} đ</span>
                            </p>
                        @else
                            <p class="mb-0 text-muted">Không có thông tin lịch khởi hành.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
