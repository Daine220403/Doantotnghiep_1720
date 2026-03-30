@extends('admin.layout.app')
@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Chi tiết tour & danh sách khách</h1>

    <div class="card mb-4">
        <div class="card-header">Thông tin tour</div>
        <div class="card-body">
            <p><strong>Mã tour:</strong> {{ $departure->tour->code ?? '-' }}</p>
            <p><strong>Tên tour:</strong> {{ $departure->tour->title ?? '-' }}</p>
            <p><strong>Thời gian:</strong> {{ $departure->start_date }} - {{ $departure->end_date }}</p>
            <p><strong>Điểm tập trung:</strong> {{ $departure->meeting_point }}</p>
            <p><strong>Trạng thái:</strong> {{ $departure->status }}</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Danh sách khách theo lịch khởi hành</span>
            <a href="{{ route('guide.departures.report', $departure->id) }}" class="btn btn-sm btn-secondary">Xem báo cáo thống kê</a>
        </div>
        <div class="card-body">
            @php
                $allPassengers = $departure->bookings->flatMap->passengers;
            @endphp

            @if($allPassengers->isEmpty())
                <div class="alert alert-info">Chưa có khách nào cho lịch khởi hành này.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Họ tên</th>
                                <th>Giới tính</th>
                                <th>Ngày sinh</th>
                                <th>Loại khách</th>
                                <th>Số giấy tờ</th>
                                <th>Phòng đơn</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($departure->bookings as $booking)
                                @foreach($booking->passengers as $p)
                                    <tr>
                                        <td>{{ $p->full_name }}</td>
                                        <td>{{ $p->gender ?? '-' }}</td>
                                        <td>{{ $p->dob ?? '-' }}</td>
                                        <td>{{ $p->passenger_type }}</td>
                                        <td>{{ $p->id_no ?? '-' }}</td>
                                        <td>{{ $p->single_room ? 'Có' : 'Không' }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
