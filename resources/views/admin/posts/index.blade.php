@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Quản lý bài viết</h1>
                <p class="mb-0 text-muted">
                    Danh sách các bài viết trên website.
                </p>
            </div>

            <a href="{{ route('admin.posts.create') }}" class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Thêm bài viết
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

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm align-middle" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 30%;">Tiêu đề</th>
                                <th style="width: 15%;">Danh mục</th>
                                <th style="width: 15%;">Tác giả</th>
                                <th style="width: 15%;">Ngày đăng</th>
                                <th style="width: 10%;">Trạng thái</th>
                                <th style="width: 15%;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $index = ($posts->currentPage() - 1) * $posts->perPage() + 1; @endphp
                            @forelse ($posts as $post)
                                <tr>
                                    <td class="text-center">{{ $index++ }}</td>
                                    <td class="text-left font-weight-bold">
                                        <span title="{{ $post->title }}">{{ Str::limit($post->title, 40) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info">{{ $post->category }}</span>
                                    </td>
                                    <td class="text-center">{{ $post->author ? $post->author->name : 'N/A' }}</td>
                                    <td class="text-center">
                                        @if ($post->published_at)
                                            {{ $post->published_at->format('d/m/Y') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($post->is_published)
                                            <span class="badge badge-success">Đã đăng</span>
                                        @else
                                            <span class="badge badge-warning">Nháp</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.posts.show', $post->id) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.posts.edit', $post->id) }}" class="btn btn-sm btn-primary" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.posts.destroy', $post->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa bài viết này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Chưa có bài viết nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $posts->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
