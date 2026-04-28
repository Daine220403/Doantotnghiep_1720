@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Báo cáo công việc của tôi</h1>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Danh sách báo cáo</h6>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th>Tiêu đề</th>
                                <th>File</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reports as $report)
                                <tr>
                                    <td>{{ $report->report_date->format('d/m/Y') }}</td>
                                    <td>{{ $report->title }}</td>
                                    <td>
                                        @if($report->file_path)
                                            <a href="{{ asset('storage/' . $report->file_path) }}" target="_blank">Tải xuống</a>
                                        @else
                                            <span class="text-muted">Không có file</span>
                                        @endif
                                    </td>
                                    <td>{{ $report->status }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Chưa có báo cáo nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{ $reports->links() }}
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Gửi báo cáo mới</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('guide.reports.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="report_date">Ngày báo cáo</label>
                            <input type="date" class="form-control" id="report_date" name="report_date" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="form-group">
                            <label for="title">Tiêu đề</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="report_file">File báo cáo</label>
                            {{-- chỉ up file word hoặc pdf --}}
                            <input type="file" class="form-control" id="report_file" name="report_file" accept=".pdf,.doc,.docx" required>
                            <small class="form-text text-muted">Hỗ trợ: pdf, doc, docx.</small>
                        </div>
                        <div class="form-group">
                            <label for="content">Ghi chú (tuỳ chọn)</label>
                            <textarea class="form-control" id="content" name="content" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Gửi báo cáo</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
