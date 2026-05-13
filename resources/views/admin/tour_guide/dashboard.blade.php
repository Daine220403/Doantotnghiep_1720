@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard Hướng Dẫn Viên</h1>
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
                                    Tổng số tour được phân công</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalAssignedDepartures }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-map fa-2x text-gray-300"></i>
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
                                    Tour đang hoạt động</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeOrUpcomingCount }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                    Khách sắp tới (7 ngày)</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPassengersUpcoming }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                    Tour hoàn thành</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $completedDepartures->count() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check fa-2x text-gray-300"></i>
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
                                            <th>Ngày về</th>
                                            <th>Khách</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($upcomingDepartures as $departure)
                                            <tr>
                                                <td>
                                                    <a href="#" class="text-decoration-none">
                                                        {{ $departure->tour->title ?? $departure->tour->code ?? '#' }}
                                                    </a>
                                                </td>
                                                <td>{{ optional($departure->start_date)->format('d/m/Y') ?? '-' }}</td>
                                                <td>{{ optional($departure->end_date)->format('d/m/Y') ?? '-' }}</td>
                                                <td>{{ $departure->bookings->sum('passenger_count') ?? 0 }}/{{ $departure->capacity_total }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $departure->status === 'pending' ? 'warning' : ($departure->status === 'confirmed' ? 'info' : 'success') }}">
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
                        <h6 class="m-0 font-weight-bold text-primary">Tour hoàn thành gần đây</h6>
                    </div>
                    <div class="card-body">
                        @if($completedDepartures->isEmpty())
                            <p class="text-muted mb-0">Chưa có tour hoàn thành</p>
                        @else
                            <ul class="list-unstyled mb-0">
                                @foreach($completedDepartures as $departure)
                                    <li class="mb-2 pb-2 border-bottom">
                                        <div class="font-weight-bold">{{ $departure->tour->title ?? $departure->tour->code ?? '#' }}</div>
                                        <small class="text-muted">
                                            {{ optional($departure->end_date)->format('d/m/Y') ?? '-' }}
                                        </small>
                                    </li>
                                @endforeach
                            </ul>
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
                                            <th>Ngày về</th>
                                            <th>Tổng khách</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($runningDepartures as $departure)
                                            <tr>
                                                <td>{{ $departure->tour->title ?? $departure->tour->code ?? '#' }}</td>
                                                <td>{{ optional($departure->start_date)->format('d/m/Y') ?? '-' }}</td>
                                                <td>{{ optional($departure->end_date)->format('d/m/Y') ?? '-' }}</td>
                                                <td>{{ $departure->bookings->sum('passenger_count') ?? 0 }}</td>
                                                <td>
                                                    <a href="{{ route('guide.show', $departure->id) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> Chi tiết
                                                    </a>
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
    </div>
@endsection
