@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Quản lý đối tác dịch vụ</h1>
                <p class="mb-0 text-muted">
                    Danh sách các đối tác cung cấp dịch vụ cho tour.
                </p>
            </div>

            <a href="{{ route('admin.mana-partner.create') }}" class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Thêm đối tác
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

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm text-center align-middle" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Tên đối tác</th>
                                <th>Loại</th>
                                <th>Điện thoại</th>
                                <th>Email</th>
                                <th>Trạng thái</th>
                                <th>Dịch vụ</th>
                                <th style="width: 140px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $index = 1; @endphp
                            @forelse ($partners as $partner)
                                <tr>
                                    <td>{{ $index++ }}</td>
                                    <td class="text-left font-weight-bold">{{ $partner->name }}</td>
                                    <td>
                                        @switch($partner->type)
                                            @case('hotel') Khách sạn @break
                                            @case('transport') Vận chuyển @break
                                            @case('restaurant') Nhà hàng @break
                                            @case('attraction') Điểm tham quan @break
                                            @default Khác
                                        @endswitch
                                    </td>
                                    <td>{{ $partner->phone }}</td>
                                    <td>{{ $partner->email }}</td>
                                    <td>
                                        @if ($partner->status === 'active')
                                            <span class="badge badge-success">Hoạt động</span>
                                        @elseif ($partner->status === 'inactive')
                                            <span class="badge badge-secondary">Tạm dừng</span>
                                        @else
                                            <span class="badge badge-danger">Khóa</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.mana-partner.services', $partner->id) }}" class="btn btn-sm btn-info">
                                            {{ $partner->services_count }} dịch vụ
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.mana-partner.edit', $partner->id) }}" class="btn btn-sm btn-primary" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.mana-partner.destroy', $partner->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa đối tác này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Chưa có đối tác nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
