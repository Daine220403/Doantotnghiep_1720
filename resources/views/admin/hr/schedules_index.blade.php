@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Lịch làm việc nhân viên</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách lịch làm việc</h6>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th>Nhân viên</th>
                        <th>Quản lý</th>
                        <th>Ca</th>
                        <th>Giờ</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->work_date->format('d/m/Y') }}</td>
                            <td>{{ optional($schedule->staff)->name }}</td>
                            <td>{{ optional($schedule->manager)->name }}</td>
                            <td>{{ $schedule->shift_type }}</td>
                            <td>{{ $schedule->start_time }} - {{ $schedule->end_time }}</td>
                            <td>{{ $schedule->status }}</td>
                            <td>{{ $schedule->note }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Chưa có dữ liệu lịch làm việc.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $schedules->links() }}
        </div>
    </div>
</div>
@endsection
