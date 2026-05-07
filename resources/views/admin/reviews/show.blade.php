@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Chi tiết đánh giá #{{ $review->id }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reviews.index') }}">Đánh giá</a></li>
                    <li class="breadcrumb-item active">Chi tiết</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Thông tin đánh giá -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin đánh giá</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Trạng thái:</strong>
                            @if($review->status === 'pending')
                                <span class="badge badge-warning">Chờ duyệt</span>
                            @elseif($review->status === 'approved')
                                <span class="badge badge-success">Đã duyệt</span>
                            @elseif($review->status === 'hidden')
                                <span class="badge badge-danger">Đã từ chối</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Ngày tạo:</strong> {{ $review->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Rating:</strong>
                            <div class="text-warning d-inline">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star{{ $i <= $review->rating ? '' : '-half-alt' }}"></i>
                                @endfor
                            </div>
                            <span class="ml-2">{{ $review->rating }}/5</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Cập nhật:</strong> {{ $review->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Nội dung đánh giá:</strong>
                        <div class="mt-2 p-3 bg-light rounded">
                            {!! nl2br(e($review->content)) !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin booking -->
            @if($review->booking)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin booking</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Mã booking:</strong> {{ $review->booking->booking_code ?? 'N/A' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Trạng thái booking:</strong>
                            @if($review->booking->status === 'confirmed')
                                <span class="badge badge-success">Đã xác nhận</span>
                            @elseif($review->booking->status === 'pending')
                                <span class="badge badge-warning">Chờ xử lý</span>
                            @elseif($review->booking->status === 'cancelled')
                                <span class="badge badge-danger">Đã hủy</span>
                            @endif
                        </div>
                    </div>

                    @if($review->booking->departure)
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Ngày khởi hành:</strong> {{ $review->booking->departure->departure_date ? \Carbon\Carbon::parse($review->booking->departure->departure_date)->format('d/m/Y') : 'N/A' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Trạng thái khởi hành:</strong>
                            @if($review->booking->departure->status === 'completed')
                                <span class="badge badge-success">Đã hoàn thành</span>
                            @elseif($review->booking->departure->status === 'active')
                                <span class="badge badge-info">Đang diễn ra</span>
                            @elseif($review->booking->departure->status === 'upcoming')
                                <span class="badge badge-warning">Sắp tới</span>
                            @elseif($review->booking->departure->status === 'cancelled')
                                <span class="badge badge-danger">Đã hủy</span>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($review->booking->order)
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Tổng tiền:</strong> {{ number_format($review->booking->order->total_amount ?? 0) }} VND
                        </div>
                        <div class="col-md-6">
                            <strong>Đã thanh toán:</strong> {{ number_format($review->booking->order->paid_amount ?? 0) }} VND
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Thông tin khách hàng -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Khách hàng</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-circle bg-primary text-white mx-auto" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                            {{ strtoupper(substr($review->user->name ?? 'N', 0, 1)) }}
                        </div>
                    </div>
                    <h5 class="text-center">{{ $review->user->name ?? 'N/A' }}</h5>
                    <p class="text-center text-muted">{{ $review->user->email ?? '' }}</p>
                    <p class="text-center text-muted">{{ $review->user->phone ?? '' }}</p>
                </div>
            </div>

            <!-- Thông tin tour -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tour</h6>
                </div>
                <div class="card-body">
                    <h6>{{ $review->tour->title ?? 'N/A' }}</h6>
                    <p class="text-muted">{{ Str::limit($review->tour->description ?? '', 100) }}</p>
                    <div class="row text-center">
                        <div class="col-6">
                            <strong>Giá:</strong><br>
                            {{ number_format($review->tour->price ?? 0) }} VND
                        </div>
                        <div class="col-6">
                            <strong>Thời gian:</strong><br>
                            {{ $review->tour->duration ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thao tác -->
            @if($review->status === 'pending')
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thao tác</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-check"></i> Duyệt đánh giá
                        </button>
                    </form>

                    <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#rejectModal">
                        <i class="fas fa-times"></i> Từ chối đánh giá
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal từ chối -->
@if($review->status === 'pending')
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Từ chối đánh giá</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.reviews.reject', $review) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="reason">Lý do từ chối (tùy chọn)</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Nhập lý do từ chối..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Từ chối</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection