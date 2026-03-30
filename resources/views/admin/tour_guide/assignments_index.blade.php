
@extends('admin.layout.app')
@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Danh sách tour được phân công</h1>

    @if($departures->isEmpty())
        <div class="alert alert-info">Hiện tại bạn chưa được phân công tour nào.</div>
    @else
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Mã tour</th>
                                <th>Tên tour</th>
                                <th>Ngày khởi hành</th>
                                <th>Ngày kết thúc</th>
                                <th>Điểm tập trung</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($departures as $departure)
                                <tr>
                                    <td>{{ $departure->tour->code ?? '-' }}</td>
                                    <td>{{ $departure->tour->title ?? '-' }}</td>
                                    <td>{{ $departure->start_date }}</td>
                                    <td>{{ $departure->end_date }}</td>
                                    <td>{{ $departure->meeting_point }}</td>
                                    <td>{{ $departure->status }}</td>
                                    <td>
                                        <a href="{{ route('guide.departures.show', $departure->id) }}" class="btn btn-sm btn-primary">Xem chi tiết</a>
                                        <a href="{{ route('guide.departures.report', $departure->id) }}" class="btn btn-sm btn-secondary">Báo cáo</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
