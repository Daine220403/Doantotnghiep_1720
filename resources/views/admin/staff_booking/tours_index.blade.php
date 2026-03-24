@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Danh sách tour khách đã/ có thể đặt</h1>
                <p class="mb-0 text-muted">
                    Nhân viên chọn tour để xem các lịch khởi hành, danh sách khách đã đặt và thao tác đặt/huỷ tour giúp khách.
                </p>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTable" class="table table-sm table-bordered table-hover align-middle" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr class="text-center">
                                <th>#</th>
                                <th>Mã tour</th>
                                <th>Tên tour</th>
                                <th>Điểm đi</th>
                                <th>Điểm đến</th>
                                <th>Thời gian</th>
                                <th>Số lịch khởi hành</th>
                                <th>Tổng booking</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tours as $index => $tour)
                                @php
                                    $totalDepartures = $tour->departures->count();
                                    $totalBookings = $tour->bookings_count ?? 0;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="text-center">{{ $tour->code }}</td>
                                    <td>{{ $tour->title }}</td>
                                    <td class="text-center">{{ $tour->departure_location }}</td>
                                    <td class="text-center">{{ $tour->destination_text }}</td>
                                    <td class="text-center">
                                        {{ $tour->duration_days }}N{{ $tour->duration_nights }}Đ
                                    </td>
                                    <td class="text-center">{{ $totalDepartures }}</td>
                                    <td class="text-center">{{ $totalBookings }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.staff-booking.tours.show', $tour->id) }}" class="btn btn-sm btn-primary">
                                            Xem chi tiết &amp; đặt/huỷ
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Chưa có tour nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
