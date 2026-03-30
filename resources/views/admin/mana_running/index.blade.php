@extends('admin.layout.app')

@section('content')
	<div class="container-fluid">
		<div class="d-sm-flex align-items-center justify-content-between mb-4">
			<div>
				<h1 class="h3 mb-1 text-gray-800">Theo dõi tour đang chạy</h1>
				<p class="mb-0 text-muted">
					Danh sách các lịch khởi hành đã chốt đoàn / đang chạy. Bạn có thể xem chi tiết hành khách, HDV và
					dịch vụ liên quan.
				</p>
			</div>
		</div>

		{{-- Flash message --}}
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
								<th>Tour</th>
								<th>Ngày khởi hành</th>
								<th>Ngày kết thúc</th>
								<th>Điểm tập trung</th>
								<th>Số chỗ / Đã đặt</th>
								<th>Trạng thái</th>
								<th style="width: 180px;">Hành động</th>
							</tr>
						</thead>
						<tbody>
							@php $index = 1; @endphp
							@forelse ($departures as $departure)
								<tr>
									<td>{{ $index++ }}</td>
									<td class="text-left">
										<div class="font-weight-bold">
											{{ $departure->tour->title ?? 'N/A' }}
										</div>
									</td>
									<td>{{ \Carbon\Carbon::parse($departure->start_date)->format('d/m/Y') }}</td>
									<td>{{ \Carbon\Carbon::parse($departure->end_date)->format('d/m/Y') }}</td>
									<td class="text-left">{{ $departure->meeting_point }}</td>
									<td>{{ $departure->capacity_booked }} / {{ $departure->capacity_total }}</td>
									<td>
										@if ($departure->status === 'confirmed')
											<span class="badge badge-info">Đã chốt đoàn</span>
										@elseif ($departure->status === 'running')
											<span class="badge badge-primary">Đang chạy</span>
										@elseif ($departure->status === 'completed')
											<span class="badge badge-success">Hoàn thành</span>
										@else
											<span class="badge badge-secondary">{{ $departure->status }}</span>
										@endif
									</td>
									<td>
										<a href="{{ route('admin.running-tours.show', $departure->id) }}"
										   class="btn btn-sm btn-warning mb-1" title="Xem chi tiết tour đang chạy">
											<i class="fas fa-eye"></i> Chi tiết
										</a>
										<a href="{{ route('admin.departures.services.index', $departure->id) }}"
										   class="btn btn-sm btn-primary mb-1" title="Dịch vụ đối tác">
											<i class="fas fa-handshake"></i>
										</a>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="9" class="text-center text-muted">
										Chưa có lịch khởi hành nào được chốt / đang chạy.
									</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
@endsection

