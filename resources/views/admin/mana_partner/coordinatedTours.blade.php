@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Theo dõi tour đang chạy</h1>
                <p class="mb-0 text-muted">
                    Danh sách các lịch khởi hành đã chốt / đang chạy, kèm dịch vụ đối tác.
                </p>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm text-center align-middle" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Tour</th>
                                <th>Ngày khởi hành</th>
                                <th>Ngày kết thúc</th>
                                <th>Điểm tập trung</th>
                                <th>Số chỗ / Đã đặt</th>
                                <th>Trạng thái</th>
                                <th style="width: 150px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $index = 1; @endphp
                            @forelse ($departures as $departure)
                                <tr>
                                    <td>{{ $index++ }}</td>
                                    <td class="text-left">
                                        <div class="font-weight-bold">{{ $departure->tour->title ?? 'N/A' }}</div>
                                    </td>
                                    <td>{{ $departure->start_date }}</td>
                                    <td>{{ $departure->end_date }}</td>
                                    <td>{{ $departure->meeting_point }}</td>
                                    <td>{{ $departure->capacity_booked }} / {{ $departure->capacity_total }}</td>
                                    <td>
                                        @if ($departure->status === 'confirmed')
                                            <span class="badge badge-info">Đã chốt đoàn</span>
                                        @elseif ($departure->status === 'completed')
                                            <span class="badge badge-success">Hoàn thành</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $departure->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.departures.services.index', $departure->id) }}" class="btn btn-sm btn-primary" title="Dịch vụ đối tác">
                                            <i class="fas fa-handshake"></i> Dịch vụ đối tác
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Chưa có lịch khởi hành nào được chốt.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
