@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Danh sách nhân viên</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Nhân viên & phòng ban</h6>
            <form method="GET" class="form-inline">
                <div class="form-group mr-2">
                    <label for="department_id" class="mr-2 small mb-0">Phòng ban</label>
                    <select name="department_id" id="department_id" class="form-control form-control-sm">
                        <option value="">Tất cả</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" {{ (int) $selectedDepartmentId === $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mr-2">
                    <label for="keyword" class="mr-2 small mb-0">Từ khóa</label>
                    <input type="text" name="keyword" id="keyword" value="{{ $keyword }}" class="form-control form-control-sm" placeholder="Tên / Email / SĐT">
                </div>
                <button type="submit" class="btn btn-sm btn-outline-primary">Lọc</button>
            </form>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-bordered table-sm align-middle">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Số điện thoại</th>
                        <th>Vai trò</th>
                        <th>Phòng ban</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staffs as $index => $staff)
                        <tr>
                            <td>{{ $staffs->firstItem() + $index }}</td>
                            <td>{{ $staff->name }}</td>
                            <td>{{ $staff->email }}</td>
                            <td>{{ $staff->phone }}</td>
                            <td>{{ $staff->role }}</td>
                            <td>{{ optional($staff->department)->name ?? 'Chưa gán' }}</td>
                            <td>
                                <span class="badge badge-{{ $staff->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ $staff->status === 'active' ? 'Đang hoạt động' : 'Ngưng hoạt động' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Chưa có nhân viên phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-end">
                {{ $staffs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
