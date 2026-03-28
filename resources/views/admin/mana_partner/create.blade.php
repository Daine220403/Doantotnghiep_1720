@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <h3 class="mb-4">
            {{ isset($partner) ? 'Cập nhật đối tác dịch vụ' : 'Thêm đối tác dịch vụ' }}
        </h3>

        <form action="{{ isset($partner) ? route('admin.mana-partner.update', $partner->id) : route('admin.mana-partner.store') }}" method="POST">
            @csrf
            @if (isset($partner))
                @method('PUT')
            @endif

            <div class="card mb-4">
                <div class="card-header font-weight-bold">Thông tin đối tác</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Tên đối tác</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $partner->name ?? '') }}">
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Loại đối tác</label>
                            <select name="type" class="form-control">
                                @php $type = old('type', $partner->type ?? 'other'); @endphp
                                <option value="hotel" {{ $type === 'hotel' ? 'selected' : '' }}>Khách sạn</option>
                                <option value="transport" {{ $type === 'transport' ? 'selected' : '' }}>Vận chuyển</option>
                                <option value="restaurant" {{ $type === 'restaurant' ? 'selected' : '' }}>Nhà hàng</option>
                                <option value="attraction" {{ $type === 'attraction' ? 'selected' : '' }}>Điểm tham quan</option>
                                <option value="other" {{ $type === 'other' ? 'selected' : '' }}>Khác</option>
                            </select>
                            @error('type')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Điện thoại</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $partner->phone ?? '') }}">
                            @error('phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $partner->email ?? '') }}">
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label>Địa chỉ</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $partner->address ?? '') }}">
                            @error('address')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Trạng thái</label>
                            @php $status = old('status', $partner->status ?? 'active'); @endphp
                            <select name="status" class="form-control">
                                <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
                                <option value="locked" {{ $status === 'locked' ? 'selected' : '' }}>Khóa</option>
                            </select>
                            @error('status')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Lưu</button>
            <a href="{{ route('admin.mana-partner.index') }}" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
@endsection
