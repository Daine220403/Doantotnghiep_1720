@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard Quản Lý Tour</h1>
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
                                    Tổng số tour</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalTours }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-route fa-2x text-gray-300"></i>
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
                                    Lịch khởi hành sắp tới (30 ngày)</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $upcomingDeparturesCount }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
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
                                    Doanh thu tháng này</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($monthlyRevenue, 0, ',', '.') }} đ
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-money-bill fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Tỷ lệ đặt tour</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $bookingRate }}%</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-percent fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Lịch khởi hành sắp tới (7 ngày)</h6>
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
                                            <th>Hướng dẫn viên</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($upcomingDepartures as $departure)
                                            <tr>
                                                <td>{{ $departure->tour->title ?? $departure->tour->code ?? '#' }}</td>
                                                <td>{{ optional($departure->start_date)->format('d/m/Y') ?? '-' }}</td>
                                                <td>{{ $departure->capacity_booked ?? 0 }}/{{ $departure->capacity_total }}</td>
                                                <td>{{ $departure->assignment->guide->name ?? 'Chưa phân công' }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $departure->status === 'pending' ? 'warning' : 'success' }}">
                                                        {{ ucfirst($departure->status) }}
                                                    </span>
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
                        <h6 class="m-0 font-weight-bold text-primary">Thống kê vận hành</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2 d-flex justify-content-between align-items-center pb-2 border-bottom">
                                <span>Tour đang chạy</span>
                                <span class="font-weight-bold badge badge-success">{{ $runningDeparturesCount }}</span>
                            </li>
                            <li class="mb-2 d-flex justify-content-between align-items-center pb-2 border-bottom">
                                <span>Booking tháng này</span>
                                <span class="font-weight-bold badge badge-primary">{{ $monthlyBookings }}</span>
                            </li>
                            <li class="mb-2 d-flex justify-content-between align-items-center pb-2 border-bottom">
                                <span>Doanh thu hôm nay</span>
                                <span class="font-weight-bold text-success">{{ number_format($dailyRevenue, 0, ',', '.') }} đ</span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center">
                                <span>Dịch vụ chờ xử lý</span>
                                <span class="font-weight-bold badge badge-warning">{{ $pendingServices }}</span>
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
                        <h6 class="m-0 font-weight-bold text-primary">Top Tour (theo booking)</h6>
                    </div>
                    <div class="card-body p-0">
                        @if($topTours->isEmpty())
                            <div class="alert alert-info m-3">
                                Chưa có dữ liệu
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Tour</th>
                                            <th>Booking</th>
                                            <th>Sức chứa/Đã đặt</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($topTours as $item)
                                            <tr>
                                                <td>
                                                    <span class="font-weight-bold">{{ $item['tour']->title ?? $item['tour']->code ?? '#' }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-primary">{{ $item['booking_count'] }}</span>
                                                </td>
                                                <td>{{ $item['capacity_booked'] }}/{{ $item['capacity_total'] }}</td>
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
                        <h6 class="m-0 font-weight-bold text-primary">Hướng dẫn viên - Tour phân công</h6>
                    </div>
                    <div class="card-body p-0">
                        @if($guides->isEmpty())
                            <div class="alert alert-info m-3">
                                Chưa có hướng dẫn viên
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($guides as $guide)
                                    <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="font-weight-bold">{{ $guide->name }}</div>
                                            <small class="text-muted">{{ $guide->phone ?? '-' }}</small>
                                        </div>
                                        <span class="badge badge-success">{{ $guide->guideAssignments->count() }} tour</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($runningDepartures->isNotEmpty())
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Tour đang chạy</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Tour</th>
                                            <th>Ngày đi</th>
                                            <th>Hướng dẫn viên</th>
                                            <th>Khách</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($runningDepartures as $departure)
                                            <tr>
                                                <td>{{ $departure->tour->title ?? $departure->tour->code ?? '#' }}</td>
                                                <td>{{ optional($departure->start_date)->format('d/m/Y') ?? '-' }}</td>
                                                <td>{{ $departure->assignment->guide->name ?? 'Chưa phân công' }}</td>
                                                <td>{{ $departure->bookings->sum('passenger_count') ?? 0 }}</td>
                                                <td>
                                                    <span class="badge badge-success">{{ ucfirst($departure->status) }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Lịch khởi hành cần chốt đoàn</h6>
                    </div>
                    <div class="card-body p-0">
                        @if($needConfirmList->isEmpty())
                            <div class="alert alert-info m-3">
                                Không có lịch cần chốt đoàn
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Tour</th>
                                            <th>Ngày đi</th>
                                            <th>Khách/Sức chứa</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($needConfirmList as $departure)
                                            <tr>
                                                <td>{{ $departure->tour->title ?? $departure->tour->code ?? '#' }}</td>
                                                <td>{{ optional($departure->start_date)->format('d/m/Y') ?? '-' }}</td>
                                                <td>{{ $departure->capacity_booked ?? 0 }}/{{ $departure->capacity_total }}</td>
                                                <td>
                                                    <form action="{{ route('admin.departures.confirm', $departure->id) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Xác nhận chốt đoàn?')">
                                                            <i class="fas fa-check"></i> Chốt
                                                        </button>
                                                    </form>
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
        </div>
    </div>
@endsection
