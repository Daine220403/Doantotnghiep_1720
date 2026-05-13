@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard Quản Lý Nhân Viên</h1>
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
                                    Tổng nhân viên</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalStaff }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                    Đang làm việc hôm nay</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $staffOnDutyToday }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                    Vắng hôm nay</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $staffAbsentToday }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-slash fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Yêu cầu phép chờ duyệt</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingLeaves }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-times fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Danh sách nhân viên & chấm công hôm nay</h6>
                    </div>
                    <div class="card-body p-0">
                        @if($staffList->isEmpty())
                            <div class="alert alert-info m-3">
                                Không có nhân viên
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Tên nhân viên</th>
                                            <th>Email</th>
                                            <th>Trạng thái hôm nay</th>
                                            {{-- <th>Hành động</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($staffList as $staff)
                                            @php
                                                $attendanceToday = $staff->attendance->first();
                                                $statusBadge = !$attendanceToday 
                                                    ? 'secondary' 
                                                    : ($attendanceToday->status === 'present' ? 'success' : 'danger');
                                                $statusText = !$attendanceToday 
                                                    ? 'Chưa chấm công' 
                                                    : ($attendanceToday->status === 'present' ? 'Có mặt' : 'Vắng mặt');
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="font-weight-bold">{{ $staff->name }}</div>
                                                </td>
                                                <td>{{ $staff->email }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $statusBadge }}">{{ $statusText }}</span>
                                                </td>
                                                {{-- <td>
                                                    <a href="#" class="btn btn-sm btn-info" title="Chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td> --}}
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Yêu cầu nghỉ phép chờ duyệt</h6>
                    </div>
                    <div class="card-body p-0">
                        @if($leaveRequests->isEmpty())
                            <div class="alert alert-info m-3">
                                Không có yêu cầu chờ duyệt
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($leaveRequests as $leave)
                                    <div class="list-group-item px-3 py-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="font-weight-bold text-sm">{{ $leave->user->name }}</div>
                                                <small class="text-muted">
                                                    {{ optional($leave->start_date)->format('d/m/Y') }} - {{ optional($leave->end_date)->format('d/m/Y') }}
                                                </small>
                                            </div>
                                            <span class="badge badge-warning">{{ ucfirst($leave->status) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Lịch làm việc tuần này</h6>
                    </div>
                    <div class="card-body p-0">
                        @if($workSchedules->isEmpty())
                            <div class="alert alert-info m-3">
                                Không có lịch làm việc
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Nhân viên</th>
                                            <th>Ngày</th>
                                            <th>Ca làm việc</th>
                                            <th>Ghi chú</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($workSchedules->take(10) as $schedule)
                                            <tr>
                                                <td>{{ $schedule->user->name ?? '-' }}</td>
                                                <td>{{ optional($schedule->date)->format('d/m/Y') }}</td>
                                                <td>{{ $schedule->shift ?? '-' }}</td>
                                                <td><small>{{ Str::limit($schedule->notes ?? '', 30) }}</small></td>
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
                        <h6 class="m-0 font-weight-bold text-primary">Top nhân viên đặt tour (30 ngày)</h6>
                    </div>
                    <div class="card-body p-0">
                        @if($topStaffBookings->isEmpty())
                            <div class="alert alert-info m-3">
                                Chưa có dữ liệu
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Nhân viên</th>
                                            <th>Booking</th>
                                            <th>Doanh thu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($topStaffBookings as $item)
                                            <tr>
                                                <td>{{ $item['user']->name ?? '-' }}</td>
                                                <td>
                                                    <span class="badge badge-primary">{{ $item['count'] }}</span>
                                                </td>
                                                <td>{{ number_format($item['revenue'], 0, ',', '.') }} đ</td>
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
