@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý đánh giá</h1>
    </div>

    <!-- Thống kê -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng đánh giá</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Chờ duyệt</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Đã duyệt</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['approved'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Đã từ chối</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['hidden'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bộ lọc -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reviews.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">Tất cả</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                        <option value="hidden" {{ request('status') == 'hidden' ? 'selected' : '' }}>Đã từ chối</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="rating" class="form-label">Số sao</label>
                    <select name="rating" id="rating" class="form-control">
                        <option value="">Tất cả</option>
                        <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 sao</option>
                        <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 sao</option>
                        <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 sao</option>
                        <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 sao</option>
                        <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 sao</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block">Lọc</button>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary d-block">Xóa lọc</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Danh sách đánh giá -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách đánh giá</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Khách hàng</th>
                            <th>Tour</th>
                            <th>Rating</th>
                            <th>Nội dung</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reviews as $review)
                        <tr>
                            <td>{{ $review->id }}</td>
                            <td>
                                <div class="font-weight-bold">{{ $review->user->name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $review->user->email ?? '' }}</small>
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ Str::limit($review->tour->title ?? 'N/A', 30) }}</div>
                                <small class="text-muted">ID: {{ $review->tour_id }}</small>
                            </td>
                            <td>
                                <div class="text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star{{ $i <= $review->rating ? '' : '-half-alt' }}"></i>
                                    @endfor
                                </div>
                                <small class="text-muted">{{ $review->rating }}/5</small>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;" title="{{ $review->content }}">
                                    {{ Str::limit($review->content, 50) }}
                                </div>
                            </td>
                            <td>
                                @if($review->status === 'pending')
                                    <span class="badge badge-warning">Chờ duyệt</span>
                                @elseif($review->status === 'approved')
                                    <span class="badge badge-success">Đã duyệt</span>
                                @elseif($review->status === 'hidden')
                                    <span class="badge badge-danger">Đã từ chối</span>
                                @endif
                            </td>
                            <td>{{ $review->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.reviews.show', $review) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Không có đánh giá nào.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $reviews->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection