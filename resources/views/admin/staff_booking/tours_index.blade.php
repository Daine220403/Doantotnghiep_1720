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
                <form method="GET" action="{{ route('admin.staff-booking.tours') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="small text-muted mb-1" for="tour_type">Loại tour</label>
                            <select class="form-control" id="tour_type" name="tour_type">
                                <option value="">Tất cả</option>
                                <option value="domestic" {{ request('tour_type') === 'domestic' ? 'selected' : '' }}>Trong nước</option>
                                <option value="international" {{ request('tour_type') === 'international' ? 'selected' : '' }}>Ngoài nước</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="small text-muted mb-1" for="destination">Điểm đến</label>
                            <input
                                type="text"
                                class="form-control"
                                id="destination"
                                name="destination"
                                list="destination-options"
                                value="{{ request('destination') }}"
                                placeholder="Nhập hoặc chọn điểm đến"
                            >
                            <datalist id="destination-options">
                                @foreach ($destinations as $dest)
                                    <option value="{{ $dest }}"></option>
                                @endforeach
                            </datalist>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="small text-muted mb-1" for="duration_days">Số ngày</label>
                            <input
                                type="number"
                                min="1"
                                class="form-control"
                                id="duration_days"
                                name="duration_days"
                                value="{{ request('duration_days') }}"
                                placeholder="Ví dụ: 3"
                            >
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="small text-muted mb-1" for="price_min">Giá từ</label>
                            <input
                                type="number"
                                min="0"
                                step="1000"
                                class="form-control"
                                id="price_min"
                                name="price_min"
                                value="{{ request('price_min') }}"
                                placeholder="0"
                            >
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="small text-muted mb-1" for="price_max">Đến</label>
                            <input
                                type="number"
                                min="0"
                                step="1000"
                                class="form-control"
                                id="price_max"
                                name="price_max"
                                value="{{ request('price_max') }}"
                                placeholder="10000000"
                            >
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <button type="submit" class="btn btn-primary btn-sm mr-2">Lọc</button>
                        <a href="{{ route('admin.staff-booking.tours') }}" class="btn btn-outline-secondary btn-sm">Xóa lọc</a>
                    </div>
                </form>

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
                                    <td colspan="9" class="text-center text-muted">
                                        @if (request('destination'))
                                            Không có dữ liệu cho điểm đến "{{ request('destination') }}".
                                        @else
                                            Chưa có tour nào.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
