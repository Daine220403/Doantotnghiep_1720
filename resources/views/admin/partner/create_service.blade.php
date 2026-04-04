@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Thêm dịch vụ mới</h1>
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
                <form action="{{ route('admin.partner.services.store') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>Tên dịch vụ</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                    </div>

                    <div class="form-group">
                        <label>Loại dịch vụ</label>
                        <input type="text" name="service_type" class="form-control" value="{{ old('service_type') }}" placeholder="VD: hotel_room, transfer, meal...">
                    </div>

                    <div class="form-group">
                        <label>Đơn giá mặc định (VNĐ)</label>
                        <input type="number" step="0.01" name="unit_price" class="form-control" value="{{ old('unit_price') }}" min="0">
                    </div>

                    <div class="form-group">
                        <label>Mô tả</label>
                        <textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Trạng thái</label>
                        @php $status = old('status', 'active'); @endphp
                        <select name="status" class="form-control">
                            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">Lưu dịch vụ</button>
                    <a href="{{ route('admin.partner.services') }}" class="btn btn-secondary btn-sm">Hủy</a>
                </form>
            </div>
        </div>
    </div>
@endsection
