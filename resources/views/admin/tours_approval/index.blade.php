@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Duyệt tours</h1>
                <p class="mb-0 text-muted">Danh sách tours nhân viên tạo, đang chờ duyệt.</p>
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

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm text-center align-middle" width="100%">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Mã tour</th>
                                <th>Tên tour</th>
                                <th>Loại tour</th>
                                <th>Điểm đi - Điểm đến</th>
                                <th>Số lịch khởi hành</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tours as $index => $tour)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $tour->code }}</td>
                                    <td class="text-left">{{ $tour->title }}</td>
                                    <td>
                                        @if ($tour->tour_type === 'domestic')
                                            <span class="badge badge-primary">Trong nước</span>
                                        @else
                                            <span class="badge badge-info">Quốc tế</span>
                                        @endif
                                    </td>
                                    <td class="text-left">
                                        {{ $tour->departure_location }} → {{ $tour->destination_text }}
                                    </td>
                                    <td>{{ $tour->departures->count() }}</td>
                                    <td><span class="badge badge-secondary">Chờ duyệt</span></td>
                                    <td>{{ $tour->created_at?->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="d-flex justify-content-center">
                                            <form action="{{ route('admin.tours-approval.approve', $tour->id) }}" method="POST" class="mr-1">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success"
                                                    onclick="return confirm('Duyệt tour này và mở bán?');">
                                                    Duyệt
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.tours-approval.reject', $tour->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Từ chối tour này và chuyển về nháp?');">
                                                    Từ chối
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Hiện chưa có tour nào cần duyệt.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
