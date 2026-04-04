@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Chỉnh sửa dịch vụ</h1>
                <p class="mb-0 text-muted">
                    Đối tác: <strong>{{ $partner->name }}</strong>
                </p>
            </div>

            <a href="{{ route('admin.partner.services') }}" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại danh sách dịch vụ
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-header font-weight-bold">Thông tin dịch vụ</div>
            <div class="card-body">
                <form action="{{ route('admin.partner.services.update', $service->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Tên dịch vụ</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $service->name) }}">
                    </div>

                    <div class="form-group">
                        <label>Loại dịch vụ</label>
                        <input type="text" name="service_type" class="form-control" value="{{ old('service_type', $service->service_type) }}">
                    </div>

                    <div class="form-group">
                        <label>Đơn giá mặc định (VNĐ)</label>
                        <input type="number" step="0.01" name="unit_price" class="form-control" value="{{ old('unit_price', $service->unit_price) }}" min="0">
                    </div>

                    <div class="form-group">
                        <label>Mô tả</label>
                        <textarea name="description" rows="3" class="form-control">{{ old('description', $service->description) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Trạng thái</label>
                        @php $status = old('status', $service->status); @endphp
                        <select name="status" class="form-control">
                            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">Lưu thay đổi</button>
                    <a href="{{ route('admin.partner.services') }}" class="btn btn-secondary btn-sm">Hủy</a>
                </form>
            </div>
        </div>
    </div>
@endsection
