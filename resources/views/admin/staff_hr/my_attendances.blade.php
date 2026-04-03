@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Bảng chấm công của tôi</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách chấm công</h6>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th>Giờ vào</th>
                        <th>Giờ ra</th>
                        <th>Trạng thái</th>
                        <th>Nguồn</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->work_date->format('d/m/Y') }}</td>
                            <td>{{ optional($attendance->check_in_time)->format('H:i') }}</td>
                            <td>{{ optional($attendance->check_out_time)->format('H:i') }}</td>
                            <td>{{ $attendance->status }}</td>
                            <td>{{ $attendance->source }}</td>
                            <td>{{ $attendance->note }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Chưa có dữ liệu chấm công.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $attendances->links() }}
        </div>
    </div>
</div>
@endsection
