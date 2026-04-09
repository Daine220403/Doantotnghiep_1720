@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Danh sách tài khoản</h1>
            <a href="{{ route('admin.mana-user.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Thêm tài khoản
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Tài khoản hệ thống</h6>
                <form method="GET" class="form-inline">
                    <label class="mr-2 small text-muted">Lọc theo vai trò:</label>
                    <select name="role" class="form-control form-control-sm mr-2">
                        <option value="">Tất cả</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" {{ ($currentRole ?? '') === $role ? 'selected' : '' }}>
                                {{ $role }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-secondary">Lọc</button>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Tên</th>
                                <th>Email</th>
                                <th>Vai trò</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th class="text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $index = 1;
                            @endphp
                            @forelse ($users as $user)
                                <tr>
                                    <td>{{ $index++ }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->role }}</td>
                                    <td>
                                        @if ($user->status === 'active')
                                            <span class="badge badge-success">Đang hoạt động</span>
                                        @else
                                            <span class="badge badge-secondary">Đã vô hiệu hóa</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($user->created_at)->format('d/m/Y') }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.mana-user.show', $user) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('admin.mana-user.toggle-status', $user) }}" method="POST" class="d-inline">
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
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">Chưa có tài khoản nào phù hợp.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($users instanceof \Illuminate\Contracts\Pagination\Paginator || $users instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                <div class="card-footer py-2">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
