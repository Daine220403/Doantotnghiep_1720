@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard Nhân Viên</h1>
            <span class="d-none d-sm-inline-block text-muted">
                {{ now()->format('d/m/Y') }}
            </span>
        </div>

        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Booking cần xử lý</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPendingBookings }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-ticket-alt fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Booking hôm nay</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayBookings }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Doanh thu hôm nay</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($dailyRevenue, 0, ',', '.') }} đ
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-money-bill fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Doanh thu tuần này</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($weekRevenue, 0, ',', '.') }} đ
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>

        <div class="row">
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Booking cần xử lý</h6>
                    </div>
                    <div class="card-body p-0">
                        @if($bookingsPending->isEmpty())
                            <div class="alert alert-info m-3">
                                Không có booking cần xử lý
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Tour</th>
                                            <th>Khách</th>
                                            <th>Ngày đi</th>
                                            <th>Trạng thái</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bookingsPending as $booking)
                                            <tr>
                                                <td>{{ $booking->departure->tour->title ?? $booking->departure->tour->code ?? '#' }}</td>
                                                <td>{{ $booking->order->user->name ?? '-' }}</td>
                                                <td>{{ optional($booking->departure->start_date)->format('d/m/Y') ?? '-' }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $booking->order->status === 'pending' ? 'warning' : 'info' }}">
                                                        {{ ucfirst($booking->order->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.staff-booking.edit', $booking->id) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-5 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Thống kê tuần này</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2 d-flex justify-content-between align-items-center pb-2 border-bottom">
                                <span>Booking tuần này</span>
                                <span class="font-weight-bold badge badge-primary">{{ $weekBookings }}</span>
                            </li>
                            <li class="mb-2 d-flex justify-content-between align-items-center pb-2 border-bottom">
                                <span>Booking bị hủy (7 ngày)</span>
                                <span class="font-weight-bold badge badge-danger">{{ $cancelledBookings }}</span>
                            </li>
                            <li class="mb-2 d-flex justify-content-between align-items-center pb-2 border-bottom">
                                <span>Lịch cần chốt đoàn</span>
                                <span class="font-weight-bold badge badge-warning">{{ $needConfirmDepartures }}</span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center">
                                <span>Khách mới (tuần)</span>
                                <span class="font-weight-bold badge badge-success">{{ $recentCustomers->count() }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Lịch khởi hành sắp tới</h6>
                    </div>
                    <div class="card-body p-0">
                        @if($upcomingDepartures->isEmpty())
                            <div class="alert alert-info m-3">
                                Không có lịch khởi hành sắp tới
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Tour</th>
                                            <th>Ngày đi</th>
                                            <th>Khách</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($upcomingDepartures as $departure)
                                            <tr>
                                                <td>{{ $departure->tour->title ?? $departure->tour->code ?? '#' }}</td>
                                                <td>{{ optional($departure->start_date)->format('d/m/Y') ?? '-' }}</td>
                                                <td>{{ $departure->capacity_booked ?? 0 }}/{{ $departure->capacity_total }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Khách hàng mới</h6>
                    </div>
                    <div class="card-body p-0">
                        @if($recentCustomers->isEmpty())
                            <div class="alert alert-info m-3">
                                Không có khách mới
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($recentCustomers->take(10) as $passenger)
                                    <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="font-weight-bold">{{ $passenger->full_name ?? '-' }}</div>
                                            <small class="text-muted">
                                                @if($passenger->booking && $passenger->booking->order && $passenger->booking->order->user)
                                                    Khách: {{ $passenger->booking->order->user->phone ?? '-' }}
                                                @else
                                                    -
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
