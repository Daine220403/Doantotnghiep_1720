@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Chi tiết tài khoản</h1>
            <a href="{{ route('admin.mana-user.index') }}" class="btn btn-sm btn-secondary">Quay lại danh sách</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin chung</h6>
                <form action="{{ route('admin.mana-user.toggle-status', $user) }}" method="POST" class="mb-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-warning"
                        onclick="return confirm('Bạn có chắc muốn thay đổi trạng thái tài khoản này?');">
                        @if ($user->status === 'active')
                            Vô hiệu hóa
                        @else
                            Kích hoạt
                        @endif
                    </button>
                </form>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>ID:</strong> {{ $user->id }}</p>
                        <p><strong>Họ tên:</strong> {{ $user->name }}</p>
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                        <p><strong>Số điện thoại:</strong> {{ $user->phone ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Vai trò:</strong> {{ $user->role }}</p>
                        <p><strong>Phòng ban:</strong> {{ optional($user->department)->name ?? '-' }}</p>
                        <p><strong>Trạng thái:</strong>
                            @if ($user->status === 'active')
                                <span class="badge badge-success">Đang hoạt động</span>
                            @else
                                <span class="badge badge-secondary">Đã vô hiệu hóa</span>
                            @endif
                        </p>
                        <p><strong>Ngày tạo:</strong> {{ optional($user->created_at)->format('d/m/Y H:i') }}</p>
                        <p><strong>Lần cập nhật cuối:</strong> {{ optional($user->updated_at)->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                @if ($user->role === 'partner')
                    <hr>
                    <h5 class="mb-3">Thông tin đối tác liên kết</h5>
                    @if ($user->partner)
                        <p><strong>Tên đối tác:</strong> {{ $user->partner->name }}</p>
                        <p><strong>Loại đối tác:</strong> {{ $user->partner->type }}</p>
                        <p><strong>SĐT:</strong> {{ $user->partner->phone }}</p>
                        <p><strong>Email:</strong> {{ $user->partner->email }}</p>
                        <p><strong>Địa chỉ:</strong> {{ $user->partner->address }}</p>
                        <p><strong>Trạng thái:</strong> {{ $user->partner->status }}</p>
                    @else
                        <p class="text-muted">Tài khoản này chưa được liên kết với bản ghi đối tác nào.</p>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection
