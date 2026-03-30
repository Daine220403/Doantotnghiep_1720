@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Hồ sơ cá nhân</h1>
                <p class="mb-0 text-muted">Cập nhật thông tin tài khoản quản trị của bạn.</p>
            </div>
        </div>

        @if (session('status') === 'profile-updated')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Cập nhật hồ sơ thành công.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @elseif(session('status') === 'password-updated')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Cập nhật mật khẩu thành công.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-2">
                        <h6 class="m-0 font-weight-bold text-primary">Thông tin cơ bản</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            @method('PATCH')

                            <div class="form-group">
                                <label for="name">Họ và tên</label>
                                <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $user->name) }}" required autofocus>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Số điện thoại</label>
                                <input type="text" class="form-control" value="{{ $user->phone ?? '' }}" disabled>
                                <small class="form-text text-muted">Trường này đang cố định, cần thiết có thể bổ sung chức năng cập nhật sau.</small>
                            </div>

                            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-2">
                        <h6 class="m-0 font-weight-bold text-primary">Thông tin hệ thống</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Vai trò:</strong> {{ $user->role }}</p>
                        <p><strong>Trạng thái:</strong>
                            @if ($user->status === 'active')
                                <span class="badge badge-success">Đang hoạt động</span>
                            @else
                                <span class="badge badge-danger">Đã khóa</span>
                            @endif
                        </p>
                        <p><strong>Ngày tạo tài khoản:</strong> {{ $user->created_at?->format('d/m/Y H:i') }}</p>
                        <p><strong>Lần cập nhật gần nhất:</strong> {{ $user->updated_at?->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-2">
                        <h6 class="m-0 font-weight-bold text-primary">Đổi mật khẩu</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="current_password">Mật khẩu hiện tại</label>
                                <input id="current_password" type="password" name="current_password"
                                       class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                       required autocomplete="current-password">
                                @error('current_password', 'updatePassword')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password">Mật khẩu mới</label>
                                <input id="password" type="password" name="password"
                                       class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                       required autocomplete="new-password">
                                @error('password', 'updatePassword')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation">Xác nhận mật khẩu mới</label>
                                <input id="password_confirmation" type="password" name="password_confirmation" class="form-control"
                                       required autocomplete="new-password">
                            </div>

                            <button type="submit" class="btn btn-warning">Cập nhật mật khẩu</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
