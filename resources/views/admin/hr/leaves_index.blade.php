@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Đơn nghỉ phép</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách đơn nghỉ phép</h6>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Nhân viên</th>
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
                            <td colspan="7" class="text-center">Chưa có đơn nghỉ phép.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $leaves->links() }}
        </div>
    </div>
</div>
@endsection
