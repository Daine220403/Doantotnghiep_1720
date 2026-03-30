@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Chi tiết tour đang chạy</h1>
                <p class="mb-0 text-muted">
                    Tour: <strong>{{ $departure->tour->title ?? 'N/A' }}</strong><br>
                    Mã lịch khởi hành: #{{ $departure->id }}
                </p>
            </div>

            <a href="{{ route('admin.running-tours.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-2">
                        <h6 class="m-0 font-weight-bold text-primary">Thông tin lịch khởi hành</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Tour:</strong> {{ $departure->tour->title ?? 'N/A' }}</p>
                        <p><strong>Ngày khởi hành:</strong>
                            {{ \Carbon\Carbon::parse($departure->start_date)->format('d/m/Y') }}</p>
                        <p><strong>Ngày kết thúc:</strong>
                            {{ \Carbon\Carbon::parse($departure->end_date)->format('d/m/Y') }}</p>
                        <p><strong>Điểm tập trung:</strong> {{ $departure->meeting_point }}</p>
                        <p><strong>Số chỗ / Đã đặt:</strong>
                            {{ $departure->capacity_booked }} / {{ $departure->capacity_total }}</p>
                        <p><strong>Trạng thái:</strong>
                            @if ($departure->status === 'confirmed')
                                <span class="badge badge-info">Đã chốt đoàn</span>
                            @elseif ($departure->status === 'running')
                                <span class="badge badge-primary">Đang chạy</span>
                            @elseif ($departure->status === 'completed')
                                <span class="badge badge-success">Hoàn thành</span>
                            @else
                                <span class="badge badge-secondary">{{ $departure->status }}</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Hướng dẫn viên phụ trách</h6>
                    </div>
                    <div class="card-body">
                        @if ($departure->assignment && $departure->assignment->guide)
                            <p><strong>Họ tên:</strong> {{ $departure->assignment->guide->name }}</p>
                            <p><strong>Email:</strong> {{ $departure->assignment->guide->email }}</p>
                            @if (!empty($departure->assignment->guide->phone))
                                <p><strong>SĐT:</strong> {{ $departure->assignment->guide->phone }}</p>
                            @endif
                            <p><strong>Trạng thái phân công:</strong> {{ $departure->assignment->status ?? 'Đang phân công' }}</p>
                        @else
                            <p class="text-muted mb-0">Chưa phân công hướng dẫn viên cho lịch khởi hành này.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Danh sách hành khách tham gia tour</h6>
            </div>
            <div class="card-body">
                @php
                    $hasPassengers = false;
                    foreach ($departure->bookings as $b) {
                        if ($b->passengers->count() > 0) {
                            $hasPassengers = true;
                            break;
                        }
                    }
                @endphp

                @if (! $hasPassengers)
                    <p class="text-muted mb-0">Chưa có hành khách nào tham gia lịch khởi hành này.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-hover text-center align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Họ tên</th>
                                    <th>Loại khách</th>
                                    <th>Giới tính</th>
                                    <th>Ngày sinh</th>
                                    <th>Số giấy tờ</th>
                                    <th>Booking</th>
                                    <th>Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $stt = 1; @endphp
                                @foreach ($departure->bookings as $booking)
                                    @foreach ($booking->passengers as $p)
                                        <tr>
                                            <td>{{ $stt++ }}</td>
                                            <td class="text-left">{{ $p->full_name }}</td>
                                            <td>
                                                @switch($p->passenger_type)
                                                    @case('adult') Người lớn @break
                                                    @case('child') Trẻ em @break
                                                    @case('infant') Trẻ nhỏ @break
                                                    @case('youth') Em bé @break
                                                    @default Khác
                                                @endswitch
                                            </td>
                                            <td>
                                                @if ($p->gender === 'male')
                                                    Nam
                                                @elseif ($p->gender === 'female')
                                                    Nữ
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if ($p->dob)
                                                    {{ \Carbon\Carbon::parse($p->dob)->format('d/m/Y') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $p->id_no ?? '-' }}</td>
                                            <td>
                                                #{{ $booking->id }}<br>
                                                <span class="badge badge-light">
                                                    {{ $booking->status }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($p->single_room)
                                                    <span class="badge badge-warning">Phòng đơn</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
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
