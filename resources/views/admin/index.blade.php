@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Bảng điều khiển tổng quan</h1>
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
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalTours ?? 0 }}</div>
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
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $upcomingDeparturesCount ?? 0 }}
                                </div>
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
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Booking mới hôm nay</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayBookings ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-ticket-alt fa-2x text-gray-300"></i>
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
                                    Doanh thu ({{ $revenueFilterLabel ?? 'tháng này' }})</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ isset($monthlyRevenue) ? number_format($monthlyRevenue, 0, ',', '.') . ' đ' : '0 đ' }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-donate fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Doanh thu & đặt tour gần đây</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.index') }}" class="form-inline mb-3">
                            <label class="mr-2 small text-muted">Bộ lọc doanh thu:</label>
                            <select name="revenue_range" id="revenue-range" class="form-control form-control-sm mr-2">
                                @php($currentRange = $revenueRange ?? 'this_month')
                                <option value="today" {{ $currentRange === 'today' ? 'selected' : '' }}>Hôm nay</option>
                                <option value="this_month" {{ $currentRange === 'this_month' ? 'selected' : '' }}>Tháng này</option>
                                <option value="last_month" {{ $currentRange === 'last_month' ? 'selected' : '' }}>Tháng trước</option>
                                <option value="last_7_days" {{ $currentRange === 'last_7_days' ? 'selected' : '' }}>7 ngày qua</option>
                                <option value="last_30_days" {{ $currentRange === 'last_30_days' ? 'selected' : '' }}>30 ngày qua</option>
                                <option value="custom" {{ $currentRange === 'custom' ? 'selected' : '' }}>Khoảng tuỳ chọn</option>
                            </select>
                            <div id="revenue-custom-range" class="form-inline mb-2 mb-sm-0 {{ $currentRange === 'custom' ? '' : 'd-none' }}">
                                <input type="date" name="revenue_from" value="{{ isset($revenueFrom) ? $revenueFrom->format('Y-m-d') : '' }}" class="form-control form-control-sm mr-2">
                                <span class="mr-2">-</span>
                                <input type="date" name="revenue_to" value="{{ isset($revenueTo) ? $revenueTo->format('Y-m-d') : '' }}" class="form-control form-control-sm mr-2">
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Lọc</button>
                        </form>

                        <div class="chart-area mb-3" style="height: 220px;">
                            <canvas id="revenueLineChart"></canvas>
                        </div>

                        @isset($bookingStats)
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2 d-flex justify-content-between">
                                    <span>Booking 7 ngày qua</span>
                                    <span class="font-weight-bold">{{ $bookingStats['last_7_days'] ?? 0 }}</span>
                                </li>
                                <li class="mb-2 d-flex justify-content-between">
                                    <span>Booking 30 ngày qua</span>
                                    <span class="font-weight-bold">{{ $bookingStats['last_30_days'] ?? 0 }}</span>
                                </li>
                                <li class="d-flex justify-content-between">
                                    <span>Tỷ lệ huỷ</span>
                                    <span class="font-weight-bold">{{ $bookingStats['cancel_rate'] ?? '0%' }}</span>
                                </li>
                            </ul>
                        @else
                            <p class="mb-0 text-muted">Chưa có dữ liệu thống kê. Bạn có thể thêm logic ở controller để
                                truyền biến <code>$bookingStats</code> vào view.</p>
                        @endisset
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-5 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Trạng thái vận hành</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2 d-flex justify-content-between align-items-center">
                                <span>Tour đang chạy</span>
                                <span class="font-weight-bold">{{ $runningToursCount ?? 0 }}</span>
                            </li>
                            <li class="mb-2 d-flex justify-content-between align-items-center">
                                <span>Yêu cầu dịch vụ chờ đối tác</span>
                                <span class="font-weight-bold">{{ $pendingPartnerRequests ?? 0 }}</span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center">
                                <span>Lịch khởi hành cần chốt đoàn</span>
                                <span class="font-weight-bold">{{ $needConfirmDepartures ?? 0 }}</span>
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
                        <h6 class="m-0 font-weight-bold text-primary">Lịch khởi hành trong 7 ngày tới</h6>
                    </div>
                    <div class="card-body p-0">
                        @isset($upcomingDepartures)
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Tour</th>
                                            <th>Ngày đi</th>
                                            <th>Đã đặt</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($upcomingDepartures as $departure)
                                            <tr>
                                                <td>{{ $departure->tour->title ?? $departure->tour->code ?? '#' }}</td>
                                                <td>{{ optional($departure->start_date)->format('d/m/Y') ?? '-' }}</td>
                                                <td>{{ $departure->capacity_booked ?? 0 }}/{{ $departure->capacity_total ?? '-' }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $departure->status === 'closed' ? 'secondary' : 'success' }}">
                                                        {{ $departure->status ?? 'N/A' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-3">Không có lịch khởi hành
                                                    nào trong 7 ngày tới.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="p-3 mb-0 text-muted">Chưa truyền danh sách <code>$upcomingDepartures</code>.</p>
                        @endisset
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Booking mới nhất</h6>
                    </div>
                    <div class="card-body p-0">
                        @isset($latestBookings)
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Mã đơn</th>
                                            <th>Tour</th>
                                            <th>Ngày đặt</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($latestBookings as $booking)
                                            <tr>
                                                <td>{{ $booking->order->order_code ?? '#' }}</td>
                                                <td>{{ $booking->departure->tour->title ?? '-' }}</td>
                                                <td>{{ optional($booking->created_at)->format('d/m/Y H:i') ?? '-' }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $booking->status === 'cancelled' ? 'danger' : 'info' }}">
                                                        {{ $booking->status ?? 'N/A' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-3">Chưa có booking nào.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="p-3 mb-0 text-muted">Chưa truyền danh sách <code>$latestBookings</code>.</p>
                        @endisset
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Nhân sự & đối tác</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Nhân viên nội bộ</span>
                                    <span class="font-weight-bold">{{ $staffCount ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Hướng dẫn viên</span>
                                    <span class="font-weight-bold">{{ $guideCount ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Đối tác dịch vụ</span>
                                    <span class="font-weight-bold">{{ $partnerCount ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Đơn nghỉ phép chờ duyệt</span>
                                    <span class="font-weight-bold">{{ $pendingLeaves ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Ghi chú nhanh cho admin</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2 text-muted">Một số gợi ý thao tác nhanh:</p>
                        <ul class="mb-0">
                            <li>Kiểm tra các tour sắp khởi hành tại menu "Tour đang chạy".</li>
                            <li>Điều phối dịch vụ đối tác cho từng lịch khởi hành trong mục "Điều phối tour".</li>
                            <li>Theo dõi chấm công, lương và báo cáo công việc tại khu vực "Nhân sự".</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var rangeSelect = document.getElementById('revenue-range');
            var customRange = document.getElementById('revenue-custom-range');
            if (rangeSelect && customRange) {
                rangeSelect.addEventListener('change', function () {
                    if (this.value === 'custom') {
                        customRange.classList.remove('d-none');
                    } else {
                        customRange.classList.add('d-none');
                    }
                });
            }

            if (typeof Chart !== 'undefined') {
                var ctx = document.getElementById('revenueLineChart');
                if (ctx) {
                    var labels = @json($revenueChart['labels'] ?? []);
                    var data = @json($revenueChart['data'] ?? []);

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Doanh thu (đ)',
                                lineTension: 0.3,
                                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                                borderColor: 'rgba(78, 115, 223, 1)',
                                pointRadius: 3,
                                pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                                pointBorderColor: 'rgba(78, 115, 223, 1)',
                                pointHoverRadius: 3,
                                pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                                pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                                pointHitRadius: 10,
                                pointBorderWidth: 2,
                                data: data,
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            legend: { display: false },
                            tooltips: {
                                callbacks: {
                                    label: function (tooltipItem) {
                                        var value = tooltipItem.yLabel || 0;
                                        return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(value) + ' đ';
                                    }
                                }
                            },
                            scales: {
                                xAxes: [{
                                    gridLines: { display: false },
                                }],
                                yAxes: [{
                                    ticks: {
                                        callback: function (value) {
                                            return new Intl.NumberFormat('vi-VN').format(value / 1000000) + 'M';
                                        }
                                    }
                                }]
                            }
                        }
                    });
                }
            }
        });
    </script>
@endsection
