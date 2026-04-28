@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Đơn nghỉ phép của tôi</h1>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Lịch sử đơn nghỉ phép</h6>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Loại nghỉ</th>
                                <th>Từ ngày</th>
                                <th>Đến ngày</th>
                                <th>Lý do</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($leaves as $leave)
                                <tr>
                                    <td>{{ $leave->leave_type }}</td>
                                    <td>{{ $leave->start_date->format('d/m/Y') }}</td>
                                    <td>{{ $leave->end_date->format('d/m/Y') }}</td>
                                    <td>{{ $leave->reason }}</td>
                                    <td>{{ $leave->status }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Chưa có đơn nghỉ phép nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{ $leaves->links() }}
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Gửi đơn nghỉ phép</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('guide.leaves.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="start_date">Từ ngày</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date">Đến ngày</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                        </div>
                        <div class="form-group">
                            <label for="leave_type">Loại nghỉ</label>
                            <select class="form-control" id="leave_type" name="leave_type" required>
                                <option value="annual">Nghỉ phép năm</option>
                                <option value="sick">Nghỉ ốm</option>
                                <option value="unpaid">Nghỉ không lương</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="reason">Lý do</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Gửi đơn</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
