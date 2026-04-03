@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Kỳ lương & bảng lương</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách kỳ lương</h6>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Kỳ lương</th>
                        <th>Trạng thái</th>
                        <th>Số nhân viên</th>
                        <th>Tổng lương (demo)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($periods as $period)
                        <tr>
                            <td>{{ $period->start_date->format('d/m/Y') }} - {{ $period->end_date->format('d/m/Y') }}</td>
                            <td>{{ $period->status }}</td>
                            <td>{{ $period->items->count() }}</td>
                            <td>{{ number_format($period->items->sum('net_salary')) }} đ</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Chưa có dữ liệu kỳ lương.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $periods->links() }}
        </div>
    </div>
</div>
@endsection
