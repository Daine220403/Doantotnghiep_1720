@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Báo cáo công việc nhân viên</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách báo cáo</h6>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Ngày báo cáo</th>
                        <th>Nhân viên</th>
                        <th>Tiêu đề</th>
                        <th>Tổng việc</th>
                        <th>Số giờ</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                        <tr>
                            <td>{{ $report->report_date->format('d/m/Y') }}</td>
                            <td>{{ optional($report->staff)->name }}</td>
                            <td>{{ $report->title }}</td>
                            <td>{{ $report->total_tasks }}</td>
                            <td>{{ $report->total_hours }}</td>
                            <td>{{ $report->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Chưa có báo cáo công việc.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $reports->links() }}
        </div>
    </div>
</div>
@endsection
