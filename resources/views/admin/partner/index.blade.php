@extends('admin.layout.app')

@section('content')
	<div class="container-fluid">
		<div class="d-sm-flex align-items-center justify-content-between mb-4">
			<div>
				<h1 class="h3 mb-1 text-gray-800">Dịch vụ của bạn</h1>
				<p class="mb-0 text-muted">
					Xem danh sách các dịch vụ mà tài khoản đối tác của bạn đang cung cấp.
				</p>
			</div>
			@if ($partner)
				<a href="{{ route('admin.partner.services.create') }}" class="btn btn-primary btn-sm shadow-sm">
					<i class="fas fa-plus fa-sm text-white-50"></i> Thêm dịch vụ
				</a>
			@endif
		</div>

		@if (session('success'))
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				{{ session('success') }}
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		@endif

		@if (!$partner)
			<div class="alert alert-warning" role="alert">
				Tài khoản của bạn hiện chưa được gắn với bất kỳ đối tác dịch vụ nào. Vui lòng liên hệ quản trị viên để được cấp quyền.
			</div>
		@else
			<div class="card shadow mb-4">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div>
						<h6 class="m-0 font-weight-bold text-primary">Thông tin đối tác</h6>
					</div>
				</div>
				<div class="card-body">
					<div class="row mb-3">
						<div class="col-md-6">
							<p class="mb-1"><strong>Tên đối tác:</strong> {{ $partner->name }}</p>
							<p class="mb-1"><strong>Loại:</strong>
								@switch($partner->type)
									@case('hotel') Khách sạn @break
									@case('transport') Vận chuyển @break
									@case('restaurant') Nhà hàng @break
									@case('attraction') Điểm tham quan @break
									@default Khác
								@endswitch
							</p>
						</div>
						<div class="col-md-6">
							<p class="mb-1"><strong>Điện thoại:</strong> {{ $partner->phone }}</p>
							<p class="mb-1"><strong>Email:</strong> {{ $partner->email }}</p>
							<p class="mb-1"><strong>Trạng thái:</strong>
								@if ($partner->status === 'active')
									<span class="badge badge-success">Hoạt động</span>
								@elseif ($partner->status === 'inactive')
									<span class="badge badge-secondary">Tạm dừng</span>
								@else
									<span class="badge badge-danger">Khóa</span>
								@endif
							</p>
						</div>
					</div>
				</div>
			</div>

			<div class="card shadow mb-4">
				<div class="card-header">
					<h6 class="m-0 font-weight-bold text-primary">Danh sách dịch vụ đang cung cấp</h6>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<table class="table table-bordered table-hover table-sm text-center align-middle" width="100%" cellspacing="0">
							<thead class="bg-light">
								<tr>
									<th>#</th>
									<th>Tên dịch vụ</th>
									<th>Loại dịch vụ</th>
									<th>Đơn giá mặc định (VNĐ)</th>
									<th>Trạng thái</th>
									<th style="width: 160px;">Hành động</th>
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
										<td>
											<a href="{{ route('admin.partner.services.edit', $service->id) }}" class="btn btn-sm btn-primary mb-1">
												Sửa
											</a>
											<form action="{{ route('admin.partner.services.toggle-status', $service->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn thay đổi trạng thái dịch vụ này?');">
												@csrf
												<button type="submit" class="btn btn-sm btn-warning">
													{{ $service->status === 'active' ? 'Tạm dừng' : 'Kích hoạt' }}
												</button>
											</form>
										</td>
									</tr>
								@empty
									<tr>
										<td colspan="5" class="text-center text-muted">Bạn chưa có dịch vụ nào được cấu hình.</td>
									</tr>
								@endforelse
							</tbody>
						</table>
					</div>
				</div>
			</div>
		@endif
	</div>
@endsection

