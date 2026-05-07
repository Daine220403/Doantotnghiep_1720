
@extends('admin.layout.app')
@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Danh sách tour được phân công</h1>

    <!-- Bộ lọc -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('guide.tours.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="search" class="form-label small text-muted mb-1">Tìm kiếm (mã/tên tour)</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search"
                        value="{{ request('search') }}"
                        placeholder="Nhập mã hoặc tên tour">
                </div>

                <div class="col-md-2">
                    <label for="status" class="form-label small text-muted mb-1">Trạng thái</label>
                    <select class="form-control form-control-sm" id="status" name="status"
                        onchange="this.form.submit()">
                        <option value="">Tất cả</option>
                        @if(!empty($statuses))
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="from_date" class="form-label small text-muted mb-1">Từ ngày</label>
                    <input type="date" class="form-control form-control-sm" id="from_date" name="from_date"
                        value="{{ request('from_date') }}"
                        onchange="this.form.submit()">
                </div>

                <div class="col-md-2">
                    <label for="to_date" class="form-label small text-muted mb-1">Đến ngày</label>
                    <input type="date" class="form-control form-control-sm" id="to_date" name="to_date"
                        value="{{ request('to_date') }}"
                        onchange="this.form.submit()">
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                    <a href="{{ route('guide.tours.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-redo"></i> Xóa bộ lọc
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if($departures->isEmpty())
        <div class="alert alert-info">Hiện tại bạn chưa được phân công tour nào.</div>
    @else
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>#</th>
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
                            @php
                                $index = 1;
                            @endphp
                            @foreach($departures as $departure)
                                <tr>
                                    <td>{{ $index++ }}</td>
                                    <td>{{ $departure->tour->code ?? '-' }}</td>
                                    <td>{{ $departure->tour->title ?? '-' }}</td>
                                    <td>{{ $departure->start_date }}</td>
                                    <td>{{ $departure->end_date }}</td>
                                    <td>{{ $departure->meeting_point }}</td>
                                    <td>{{ $departure->status }}</td>
                                    <td>
                                        <a href="{{ route('guide.departures.show', $departure->id) }}" class="btn btn-sm btn-primary">Xem chi tiết</a>
                                        {{-- <a href="{{ route('guide.departures.report', $departure->id) }}" class="btn btn-sm btn-secondary">Báo cáo</a> --}}
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
