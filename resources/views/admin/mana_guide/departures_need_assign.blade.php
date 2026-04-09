@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Lịch khởi hành cần phân công HDV</h1>
                <p class="mb-0 text-muted">
                    Danh sách các lịch khởi hành sắp tới chưa có Hướng dẫn viên.
                </p>
            </div>
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
                <form method="GET" action="{{ route('admin.departures.assign-guides.index') }}" class="mb-3">
                    <div class="form-row align-items-end">
                        <div class="form-group col-md-3">
                            <label for="start_date">Từ ngày</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="end_date">Đến ngày</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="tour_name">Tên tour</label>
                            <input type="text" name="tour_name" id="tour_name" class="form-control" placeholder="Nhập tên tour" value="{{ request('tour_name') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="tour_code">Mã tour</label>
                            <input type="text" name="tour_code" id="tour_code" class="form-control" placeholder="Nhập mã tour" value="{{ request('tour_code') }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">Lọc</button>
                            <a href="{{ route('admin.departures.assign-guides.index') }}" class="btn btn-secondary btn-sm">Xóa lọc</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm text-center align-middle" width="100%">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Tour</th>
                                <th>Ngày đi</th>
                                <th>Ngày về</th>
                                <th>Điểm tập trung</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($departures as $index => $departure)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="text-left">
                                        <div class="font-weight-bold text-gray-800">{{ $departure->tour->title ?? 'N/A' }}</div>
                                        <div class="small text-muted">Mã tour: {{ $departure->tour->code ?? '-' }}</div>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($departure->start_date)->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($departure->end_date)->format('d/m/Y') }}</td>
                                    <td class="text-left">{{ $departure->meeting_point }}</td>
                                    <td>{{ $departure->status }}</td>
                                    <td>
                                        <a href="{{ route('admin.departures.assign-guides.select', $departure->id) }}"
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-user-plus"></i> Chọn HDV
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Hiện không có lịch khởi hành nào cần phân công.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
