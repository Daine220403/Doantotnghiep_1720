@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Danh sách tour</h1>
                <p class="mb-0 text-muted">
                    Chọn tour để xem chi tiết thông tin tour và danh sách khách hàng đã booking.
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
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $index = 1; @endphp
                            @forelse($tours as $tour)
                                <tr>
                                    <td class="text-center">{{ $index++ }}</td>
                                    <td class="text-center font-weight-bold">{{ $tour->code }}</td>
                                    <td class="text-left font-weight-bold text-gray-800">{{ $tour->title }}</td>
                                    <td class="text-left">{{ $tour->departure_location }}</td>
                                    <td class="text-left">{{ $tour->destination_text }}</td>
                                    <td class="text-center">
                                        {{ $tour->duration_days }}N{{ $tour->duration_nights }}Đ
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.customer-tour.show', $tour->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-users"></i> Xem khách
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Chưa có tour nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
