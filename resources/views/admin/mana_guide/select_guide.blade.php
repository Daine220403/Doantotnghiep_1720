@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Chọn Hướng dẫn viên</h1>
                <p class="mb-0 text-muted">
                    Tour: <strong>{{ $departure->tour->title ?? 'N/A' }}</strong><br>
                    Lịch khởi hành: <strong>{{ \Carbon\Carbon::parse($departure->start_date)->format('d/m/Y') }}</strong>
                    - <strong>{{ \Carbon\Carbon::parse($departure->end_date)->format('d/m/Y') }}</strong>
                </p>
            </div>

            <a href="{{ route('admin.departures.assign-guides.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách lịch
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

        <!-- Thông tin Hướng dẫn viên hiện tại -->
        @php
            // dd($departure->assignment)
        @endphp
        
        @if ($departure->assignment && $departure->assignment->guide)
            <div class="card shadow mb-4 border-left-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-2 text-primary">
                                <i class="fas fa-user-check"></i> Hướng dẫn viên hiện tại
                            </h5>
                            <p class="mb-1">
                                <strong>Tên:</strong> {{ $departure->assignment->guide->name }}
                            </p>
                            <p class="mb-1">
                                <strong>Email:</strong> {{ $departure->assignment->guide->email }}
                            </p>
                            <p class="mb-0">
                                <strong>Điện thoại:</strong> {{ $departure->assignment->guide->phone ?? '-' }}
                            </p>
                        </div>
                        <div class="col-auto">
                            <form action="{{ route('admin.departures.unassign-guide', $departure->id) }}" method="POST"
                                  onsubmit="return confirm('Hủy phân công {{ $departure->assignment->guide->name }}?');"
                                  class="mb-0">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm">
                                    <i class="fas fa-times-circle"></i> Hủy phân công
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm text-center align-middle" width="100%">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Tên HDV</th>
                                <th>Email</th>
                                <th>Điện thoại</th>
                                <th>Số lịch đang được phân công</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($guides as $index => $guide)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="text-left font-weight-bold">{{ $guide->name }}</td>
                                    <td class="text-left">{{ $guide->email }}</td>
                                    <td>{{ $guide->phone ?? '-' }}</td>
                                    <td>{{ $guide->guide_assignments_count ?? $guide->guide_assignments_count ?? $guide->guide_assignments_count }}</td>
                                    <td>
                                        <form action="{{ route('admin.departures.assign-guide', $departure->id) }}" method="POST"
                                              onsubmit="return confirm('Phân công {{ $guide->name }} cho lịch khởi hành này?');">
                                            @csrf
                                            <input type="hidden" name="guide_id" value="{{ $guide->id }}">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fas fa-user-plus"></i> Phân công
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Không có Hướng dẫn viên phù hợp (không trùng lịch, đang hoạt động).</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
