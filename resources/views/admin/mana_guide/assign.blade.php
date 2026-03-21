@extends('admin.layout.app')

@section('content')
	<div class="container-fluid">
		<div class="d-sm-flex align-items-center justify-content-between mb-4">
			<div>
				<h1 class="h3 mb-1 text-gray-800">Chọn tour để phân công</h1>
				<p class="mb-0 text-muted">
					Hướng dẫn viên: <strong>{{ $guide->name }}</strong> ({{ $guide->email }})
				</p>
			</div>

			<a href="{{ route('admin.mana-guide.index') }}" class="btn btn-secondary btn-sm">
				<i class="fas fa-arrow-left"></i> Quay lại danh sách HDV
			</a>
		</div>

		<div class="card shadow mb-4">
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-bordered table-hover table-sm text-center align-middle" id="dataTable"
						width="100%" cellspacing="0">
						<thead class="bg-light">
							<tr>
								<th>#</th>
								<th>Tên tour</th>
								<th>Điểm đi</th>
								<th>Điểm đến</th>
								<th>Thời gian</th>
								<th>Số lịch khởi hành</th>
								<th>Hành động</th>
							</tr>
						</thead>
						<tbody>
							@php $index = 1; @endphp
							@forelse ($tours as $tour)
								<tr>
									<td>{{ $index++ }}</td>
									<td class="text-left font-weight-bold">{{ $tour->title }}</td>
									<td>{{ $tour->departure_location }}</td>
									<td>{{ $tour->destination_text }}</td>
									<td>{{ $tour->duration_days }}N{{ $tour->duration_nights }}Đ</td>
									<td>{{ $tour->departures_count }}</td>
									<td>
										<a href="{{ route('admin.mana-guide.tour-departures', [$guide->id, $tour->id]) }}"
											class="btn btn-sm btn-primary">
											<i class="fas fa-calendar-alt"></i> Chọn lịch để phân công
										</a>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="7" class="text-center text-muted">Chưa có tour nào.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
@endsection
