@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Đơn nghỉ phép</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách đơn nghỉ phép</h6>
        </div>
        <div class="card-body">
            {{-- Bộ lọc đơn nghỉ phép: Phòng ban, Loại nghỉ, Từ ngày, Đến ngày --}}
            <form action="{{ route('admin.hr.leaves.index') }}" method="GET" class="mb-3 border rounded p-2 bg-light">
                <div class="form-row align-items-end">
                    <div class="col-md-3">
                        <label for="filter_department_id" class="mb-1">Phòng ban</label>
                        <select name="department_id" id="filter_department_id" class="form-control form-control-sm">
                            <option value="">-- Tất cả phòng ban --</option>
                            @isset($departments)
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" {{ ($filters['department_id'] ?? null) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            @endisset
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filter_leave_type" class="mb-1">Loại nghỉ</label>
                        <select name="leave_type" id="filter_leave_type" class="form-control form-control-sm">
                            @php
                                $leaveTypeFilter = $filters['leave_type'] ?? 'all';
                            @endphp
                            <option value="all" {{ $leaveTypeFilter === 'all' ? 'selected' : '' }}>-- Tất cả --</option>
                            @isset($leaveTypes)
                                @foreach ($leaveTypes as $type)
                                    <option value="{{ $type }}" {{ $leaveTypeFilter === $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            @endisset
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filter_from_date" class="mb-1">Từ ngày</label>
                        <input type="date" name="from_date" id="filter_from_date" class="form-control form-control-sm" value="{{ $filters['from_date'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label for="filter_to_date" class="mb-1">Đến ngày</label>
                        <input type="date" name="to_date" id="filter_to_date" class="form-control form-control-sm" value="{{ $filters['to_date'] ?? '' }}">
                    </div>
                </div>
                <div class="form-row mt-2">
                    <div class="col-md-12 text-right">
                        <button type="submit" class="btn btn-sm btn-primary">Lọc</button>
                        <a href="{{ route('admin.hr.leaves.index') }}" class="btn btn-sm btn-secondary ml-1">Xóa lọc</a>
                    </div>
                </div>
            </form>
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Nhân viên</th>
                        <th>Phòng ban</th>
                        <th>Loại nghỉ</th>
                        <th>Từ ngày</th>
                        <th>Đến ngày</th>
                        <th>Lý do</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leaves as $leave)
                        <tr>
                            <td>{{ optional($leave->staff)->name }}</td>
                            <td>{{ optional(optional($leave->staff)->department)->name }}</td>
                            <td>{{ $leave->leave_type }}</td>
                            <td>{{ $leave->start_date->format('d/m/Y') }}</td>
                            <td>{{ $leave->end_date->format('d/m/Y') }}</td>
                            <td>{{ $leave->reason }}</td>
                            <td>{{ $leave->status }}</td>
                            <td>
                                @if ($leave->status === 'pending')
                                    <form action="{{ route('admin.hr.leaves.approve', $leave) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success" type="submit">Duyệt</button>
                                    </form>
                                    <form action="{{ route('admin.hr.leaves.reject', $leave) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-danger" type="submit">Từ chối</button>
                                    </form>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Chưa có đơn nghỉ phép.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $leaves->links() }}
        </div>
    </div>
</div>
@endsection
