@extends('admin.layout.app')

@section('content')
	<div class="container-fluid">
		<div class="d-sm-flex align-items-center justify-content-between mb-4">
			<div>
				<h1 class="h3 mb-1 text-gray-800">Danh sách Hướng dẫn viên</h1>
				<p class="mb-0 text-muted">
					Quản lý tài khoản Hướng dẫn viên và theo dõi số lịch khởi hành đã được phân công.
				</p>
			</div>
		</div>

		@if (session('success'))
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				{{ session('success') }}
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		@endif

		@if (session('error'))
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				{{ session('error') }}
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		@endif

		<div class="card shadow mb-4">
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-bordered table-hover table-sm text-center align-middle" id="dataTable"
						width="100%" cellspacing="0">
						<thead class="bg-light">
							<tr>
								<th>#</th>
								<th>Tên</th>
								<th>Email</th>
								<th>Điện thoại</th>
								<th>Trạng thái</th>
								<th>Số lịch được phân công</th>
								<th>Hành động</th>
							</tr>
						</thead>
						<tbody>
							@php $index = 1; @endphp
							@forelse ($guides as $guide)
								<tr>
									<td>{{ $index++ }}</td>
									<td class="text-left font-weight-bold">{{ $guide->name }}</td>
									<td class="text-left">{{ $guide->email }}</td>
									<td>{{ $guide->phone ?? '-' }}</td>
									<td>
										@if ($guide->status === 'active')
											<span class="badge badge-success">Đang hoạt động</span>
										@else
											<span class="badge badge-secondary">Đã khóa</span>
										@endif
									</td>
									<td>{{ $guide->assignments_count }}</td>
									<td>
										<a href="{{ route('admin.mana-guide.tours', $guide->id) }}" class="btn btn-sm btn-primary">
											<i class="fas fa-route"></i> Phân công tour
										</a>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="6" class="text-center text-muted">Chưa có Hướng dẫn viên nào.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
@endsection
