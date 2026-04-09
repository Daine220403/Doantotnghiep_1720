@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Báo cáo công việc nhân viên</h1>

        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary mb-2 mb-md-0">Danh sách báo cáo</h6>
                <form method="GET" action="{{ route('admin.hr.reports.index') }}" class="form-inline">
                    <div class="form-group mr-2 mb-2 mb-md-0">
                        <label for="department_id" class="mr-2 small text-muted">Phòng ban</label>
                        <select name="department_id" id="department_id" class="form-control form-control-sm">
                            <option value="">Tất cả</option>
                            @isset($departments)
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            @endisset
                        </select>
                    </div>
                    <div class="form-group mr-2 mb-2 mb-md-0">
                        <label for="staff_id" class="mr-2 small text-muted">Nhân viên</label>
                        <select name="staff_id" id="staff_id" class="form-control form-control-sm">
                            <option value="">Tất cả</option>
                            @isset($staffs)
                                @foreach ($staffs as $staff)
                                    <option value="{{ $staff->id }}"
                                        {{ request('staff_id') == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }}
                                    </option>
                                @endforeach
                            @endisset
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Lọc</button>
                </form>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Ngày báo cáo</th>
                            <th>Nhân viên</th>
                            <th>Tiêu đề</th>
                            <th>File</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                            <tr>
                                <td>{{ $report->report_date->format('d/m/Y') }}</td>
                                <td>{{ optional($report->staff)->name }}</td>
                                <td>{{ $report->title }}</td>
                                <td>
                                    @if ($report->file_path)

                                        <a href="{{ asset('storage/' . $report->file_path) }}" download="{{ $report->title }}" target="_blank">Tải
                                            xuống</a>
                                    @else
                                        <span class="text-muted">Không có file</span>
                                    @endif
                                </td>
                                <td>{{ $report->status }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Chưa có báo cáo công việc.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $reports->links() }}
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var departmentSelect = document.getElementById('department_id');
            if (departmentSelect && departmentSelect.form) {
                departmentSelect.addEventListener('change', function () {
                    // Khi đổi phòng ban thì submit form để server render lại danh sách nhân viên tương ứng
                    this.form.submit();
                });
            }
        });
    </script>
@endsection
