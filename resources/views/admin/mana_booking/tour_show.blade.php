@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Chi tiết tour & khách hàng</h1>
                <p class="mb-0 text-muted">
                    Thông tin tour và danh sách khách hàng đã booking theo từng lịch khởi hành.
                </p>
            </div>

            <a href="{{ route('admin.customer-tour.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách tour
            </a>
        </div>

        {{-- Thông tin tour --}}
        <div class="card shadow mb-3">
            <div class="card-header py-2">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin tour</h6>
            </div>
            <div class="card-body small">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><span class="text-muted">Tên tour:</span>
                            <span class="font-weight-bold">{{ $tour->title }}</span>
                        </p>
                        <p class="mb-2"><span class="text-muted">Mã tour:</span> {{ $tour->code }}</p>
                        <p class="mb-2"><span class="text-muted">Điểm đi:</span> {{ $tour->departure_location }}</p>
                        <p class="mb-2"><span class="text-muted">Điểm đến:</span> {{ $tour->destination_text }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><span class="text-muted">Thời gian:</span>
                            {{ $tour->duration_days }}N{{ $tour->duration_nights }}Đ
                        </p>
                        <p class="mb-2"><span class="text-muted">Hình thức:</span> {{ $tour->tour_type }}</p>
                        <p class="mb-2"><span class="text-muted">Phương tiện:</span> {{ $tour->transport }}</p>
                        <p class="mb-0"><span class="text-muted">Giá từ:</span>
                            {{ number_format($tour->base_price_from, 0, ',', '.') }} đ
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Thống kê tổng quan --}}
        @php
            $departures = $tour->departures ?? collect();
            $totalDepartures = $departures->count();
            // $totalCapacity = $departures->sum('capacity_total');
            // $totalBookedSeats = $departures->sum('capacity_booked');
            $totalBookings = $departures->sum(function ($d) {
                return $d->bookings ? $d->bookings->count() : 0;
            });
            $totalPassengers = $departures->sum(function ($d) {
                if (!$d->bookings) {
                    return 0;
                }

                $activeBookings = $d->bookings->where('status', '!=', 'cancelled');

                return $activeBookings->sum(function ($b) {
                    return $b->passengers ? $b->passengers->count() : 0;
                });
            });
            $totalAdults = $departures->sum(function ($d) {
                if (!$d->bookings) {
                    return 0;
                }

                $activeBookings = $d->bookings->where('status', '!=', 'cancelled');

                return $activeBookings->sum(function ($b) {
                    return $b->passengers ? $b->passengers->where('passenger_type', 'adult')->count() : 0;
                });
            });
            $totalChildren = $departures->sum(function ($d) {
                if (!$d->bookings) {
                    return 0;
                }

                $activeBookings = $d->bookings->where('status', '!=', 'cancelled');

                return $activeBookings->sum(function ($b) {
                    return $b->passengers ? $b->passengers->where('passenger_type', 'child')->count() : 0;
                });
            });
            $totalRevenue = $departures->sum(function ($d) {
                return $d->bookings ? $d->bookings->sum(function ($b) {
                    $order = $b->order;
                    return $order && $order->status === 'paid' ? $order->total_amount : 0;
                }) : 0;
            });
        @endphp

        <div class="card shadow mb-4">
            <div class="card-header py-2">
                <h6 class="m-0 font-weight-bold text-primary">Thống kê liên quan</h6>
            </div>
            <div class="card-body small">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <p class="mb-1 text-muted">Số lịch khởi hành</p>
                        <p class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalDepartures }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <p class="mb-1 text-muted">Tổng số booking</p>
                        <p class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalBookings }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <p class="mb-1 text-muted">Tổng số khách</p>
                        <p class="mb-0 font-weight-bold text-gray-800">{{ $totalPassengers }}</p>
                        <p class="mb-0 text-xs text-muted">Người lớn: {{ $totalAdults }} | Trẻ em: {{ $totalChildren }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <p class="mb-1 text-muted">Tổng doanh thu tour</p>
                        {{-- <p class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalBookedSeats }} / {{ $totalCapacity }}</p> --}}
                        <p class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalRevenue, 0, ',', '.') }} đ</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Danh sách khách theo từng lịch khởi hành --}}
        @forelse($tour->departures as $departure)
            @php
                $bookings = $departure->bookings ?? collect();
                $currentRevenue = 0;

                foreach ($bookings as $booking) {
                    $order = $booking->order;

                    if ($order && $order->status === 'paid') {
                        $currentRevenue += $order->total_amount;
                    }
                }
            @endphp
            <div class="card shadow mb-4">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Lịch khởi hành:
                        {{ \Carbon\Carbon::parse($departure->start_date)->format('d/m/Y') }} -
                        {{ \Carbon\Carbon::parse($departure->end_date)->format('d/m/Y') }}
                    </h6>
                    <div class="small text-muted">
                        Số chỗ: {{ $departure->capacity_booked }} / {{ $departure->capacity_total }}
                        | Doanh thu: {{ number_format($currentRevenue, 0, ',', '.') }} đ
                    </div>
                </div>
                <div class="card-body">
                    @if($bookings->isEmpty())
                        <p class="mb-0 text-muted small">Chưa có khách booking cho lịch khởi hành này.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm align-middle" width="100%" cellspacing="0">
                                <thead class="bg-light">
                                    <tr class="text-center">
                                        <th>#</th>
                                        <th>Mã đơn</th>
                                        <th>Người liên hệ</th>
                                        <th>Số điện thoại</th>
                                        <th>Email</th>
                                        <th>Danh sách hành khách</th>
                                        <th>Trạng thái booking</th>
                                        <th>Ngày tạo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i = 1; @endphp
                                    @foreach($bookings as $booking)
                                        @php
                                            $order = $booking->order;
                                            $passengers = $booking->passengers ?? collect();
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
                                        <tr>
                                            <td class="text-center">{{ $i++ }}</td>
                                            <td class="text-center font-weight-bold">{{ $order->order_code ?? 'N/A' }}</td>
                                            <td class="text-left font-weight-bold">{{ $order->contact_name ?? 'N/A' }}</td>
                                            <td class="text-center">{{ $order->contact_phone ?? 'N/A' }}</td>
                                            <td class="text-center">{{ $order->contact_email ?? 'N/A' }}</td>
                                            <td class="text-left">
                                                @if($passengers->isEmpty())
                                                    <span class="text-muted small">Không có danh sách hành khách.</span>
                                                @else
                                                    <ul class="mb-0 pl-3 small">
                                                        @foreach($passengers as $p)
                                                            <li>
                                                                <span class="font-weight-bold">{{ $p->full_name }}</span>
                                                                - {{ $p->gender == 'male' ? 'Nam' : 'Nữ' }}
                                                                - {{ $p->passenger_type == 'adult' ? 'Người lớn' : 'Trẻ em' }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $statusClass[$currentStatus] ?? 'badge-light' }}">
                                                    {{ $statusLabel[$currentStatus] ?? $currentStatus }}
                                                </span>
                                            </td>
                                            <td class="text-center small">
                                                {{ $booking->created_at ? $booking->created_at->format('d/m/Y H:i') : '' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="card shadow mb-4">
                <div class="card-body">
                    <p class="mb-0 text-muted small">Tour này chưa có lịch khởi hành.</p>
                </div>
            </div>
        @endforelse
    </div>
@endsection
