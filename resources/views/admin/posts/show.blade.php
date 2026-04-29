@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Chi tiết bài viết</h1>
            </div>

            <div>
                <a href="{{ route('admin.posts.edit', $post->id) }}" class="btn btn-primary btn-sm shadow-sm">
                    <i class="fas fa-edit fa-sm text-white-50"></i> Chỉnh sửa
                </a>
                <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                    <i class="fas fa-arrow-left fa-sm"></i> Quay lại
                </a>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h2 class="h4 mb-3">{{ $post->title }}</h2>

                        @if ($post->image)
                            <div class="mb-3">
                                <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" class="img-fluid rounded" style="max-width: 500px;">
                            </div>
                        @endif

                        <div class="mb-3">
                            <strong>Mô tả:</strong>
                            <p>{{ $post->description }}</p>
                        </div>

                        <div class="mb-3">
                            <strong>Nội dung:</strong>
                            <div class="border rounded p-3" style="background-color: #f8f9fa;">
                                {!! nl2br(e($post->content)) !!}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Thông tin bài viết</h5>

                                <div class="mb-3">
                                    <strong>Danh mục:</strong>
                                    <p>
                                        <span class="badge badge-info">{{ $post->category }}</span>
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <strong>Tác giả:</strong>
                                    <p>{{ $post->author ? $post->author->name : 'N/A' }}</p>
                                </div>

                                <div class="mb-3">
                                    <strong>Trạng thái:</strong>
                                    <p>
                                        @if ($post->is_published)
                                            <span class="badge badge-success">Đã đăng</span>
                                        @else
                                            <span class="badge badge-warning">Nháp</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <strong>Ngày đăng:</strong>
                                    <p>
                                        @if ($post->published_at)
                                            {{ $post->published_at->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <strong>Lượt xem:</strong>
                                    <p>{{ $post->views ?? 0 }}</p>
                                </div>

                                <div class="mb-3">
                                    <strong>Ngày tạo:</strong>
                                    <p>{{ $post->created_at->format('d/m/Y H:i') }}</p>
                                </div>

                                <div class="mb-3">
                                    <strong>Cập nhật lần cuối:</strong>
                                    <p>{{ $post->updated_at->format('d/m/Y H:i') }}</p>
                                </div>

                                <hr>

                                <form action="{{ route('admin.posts.destroy', $post->id) }}" method="POST" class="mt-3" onsubmit="return confirm('Bạn chắc chắn muốn xóa bài viết này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-block">
                                        <i class="fas fa-trash"></i> Xóa bài viết
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
