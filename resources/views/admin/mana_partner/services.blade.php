@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Dịch vụ của đối tác</h1>
                <p class="mb-0 text-muted">
                    Đối tác: <strong>{{ $partner->name }}</strong>
                </p>
            </div>

            <a href="{{ route('admin.mana-partner.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại danh sách đối tác
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            {{-- <div class="col-md-5">
                <div class="card mb-4">
                    <div class="card-header font-weight-bold">Thêm dịch vụ cho đối tác</div>
                    <div class="card-body">
                        <form action="{{ route('admin.mana-partner.services.store', $partner->id) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Tên dịch vụ</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                                @error('name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Loại dịch vụ</label>
                                <input type="text" name="service_type" class="form-control" value="{{ old('service_type') }}" placeholder="VD: hotel_room, transfer, meal...">
                                @error('service_type')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Đơn giá mặc định (VNĐ)</label>
                                <input type="number" step="0.01" name="unit_price" class="form-control" value="{{ old('unit_price') }}" min="0">
                                @error('unit_price')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Mô tả</label>
                                <textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Trạng thái</label>
                                @php $status = old('status', 'active'); @endphp
                                <select name="status" class="form-control">
                                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Hoạt động</option>
                                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
                                </select>
                                @error('status')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm">Lưu dịch vụ</button>
                        </form>
                    </div>
                </div>
            </div> --}}

            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header font-weight-bold">Danh sách dịch vụ</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm text-center align-middle" width="100%" cellspacing="0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Tên dịch vụ</th>
                                        <th>Loại</th>
                                        <th>Đơn giá (VNĐ)</th>
                                        <th>Trạng thái</th>
                                        {{-- <th style="width: 80px;">Hành động</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $index = 1; @endphp
                                    @forelse ($partner->services as $service)
                                        <tr>
                                            <td>{{ $index++ }}</td>
                                            <td class="text-left font-weight-bold">{{ $service->name }}</td>
                                            <td>{{ $service->service_type }}</td>
                                            <td class="text-right">{{ number_format($service->unit_price ?? 0, 0, ',', '.') }}</td>
                                            <td>
                                                @if ($service->status === 'active')
                                                    <span class="badge badge-success">Hoạt động</span>
                                                @else
                                                    <span class="badge badge-secondary">Tạm dừng</span>
                                                @endif
                                            </td>
                                            {{-- <td>
                                                <form action="{{ route('admin.mana-partner.services.destroy', $service->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa dịch vụ này?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td> --}}
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Chưa có dịch vụ nào.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
