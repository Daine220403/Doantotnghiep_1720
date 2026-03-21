@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Phân công vào lịch khởi hành</h1>
                <p class="mb-0 text-muted">
                    Hướng dẫn viên: <strong>{{ $guide->name }}</strong> ({{ $guide->email }})<br>
                    Tour: <strong>{{ $tour->title }}</strong>
                </p>
            </div>

            <a href="{{ route('admin.mana-guide.tours', $guide->id) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Chọn tour khác
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm text-center align-middle" id="dataTable"
                        width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Ngày đi</th>
                                <th>Ngày về</th>
                                <th>Điểm tập trung</th>
                                <th>Tổng chỗ</th>
                                <th>Đã đặt</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $index = 1; @endphp
                            @forelse ($tour->departures as $departure)
                                <tr>
                                    <td>{{ $index++ }}</td>
                                    <td>{{ \Carbon\Carbon::parse($departure->start_date)->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($departure->end_date)->format('d/m/Y') }}</td>
                                    <td class="text-left">{{ $departure->meeting_point }}</td>
                                    <td>{{ $departure->capacity_total }}</td>
                                    <td>{{ $departure->capacity_booked }}</td>
                                    <td>{{ $departure->status }}</td>
                                    <td>
                                        @php
                                            $currentAssignment = $departure->assignment;
                                        @endphp

                                        @if ($currentAssignment && $currentAssignment->guide_id == $guide->id)
                                            {{-- Đã phân công HDV này cho lịch này: chỉ cho phép hủy phân công --}}
                                            <form action="{{ route('admin.departures.assign-guide', $departure->id) }}" method="POST"
                                                onsubmit="return confirm('Hủy phân công {{ $guide->name }} cho lịch khởi hành này?');">
                                                @csrf
                                                <input type="hidden" name="guide_id" value="">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-user-times"></i> Hủy phân công
                                                </button>
                                            </form>
                                        @else
                                            {{-- Chưa phân công HDV này cho lịch này: hiển thị nút phân công --}}
                                            <form action="{{ route('admin.departures.assign-guide', $departure->id) }}" method="POST"
                                                onsubmit="return confirm('Phân công {{ $guide->name }} cho lịch khởi hành này?');">
                                                @csrf
                                                <input type="hidden" name="guide_id" value="{{ $guide->id }}">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-user-plus"></i> Phân công
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Tour này chưa có lịch khởi hành.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
