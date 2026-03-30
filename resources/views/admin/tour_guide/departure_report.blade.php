@extends('admin.layout.app')
@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Báo cáo thống kê tour</h1>

    <div class="card mb-4">
        <div class="card-header">Thông tin chung</div>
        <div class="card-body">
            <p><strong>Mã tour:</strong> {{ $departure->tour->code ?? '-' }}</p>
            <p><strong>Tên tour:</strong> {{ $departure->tour->title ?? '-' }}</p>
            <p><strong>Thời gian:</strong> {{ $departure->start_date }} - {{ $departure->end_date }}</p>
            <p><strong>Tổng số đơn đặt:</strong> {{ $totalBookings }}</p>
            <p><strong>Tổng số khách:</strong> {{ $totalPassengers }}</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Cơ cấu khách</div>
        <div class="card-body">
            <ul>
                <li>Người lớn: {{ $byType['adult'] ?? 0 }}</li>
                <li>Trẻ em: {{ $byType['child'] ?? 0 }}</li>
                <li>Trẻ nhỏ: {{ $byType['infant'] ?? 0 }}</li>
                <li>Em bé: {{ $byType['youth'] ?? 0 }}</li>
            </ul>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Phòng đơn</div>
        <div class="card-body">
            <p><strong>Số lượng khách phòng đơn:</strong> {{ $singleRoomCount }}</p>
            <p><strong>Tổng phụ thu phòng đơn:</strong> {{ number_format($singleRoomSurchargeTotal, 0, ',', '.') }} đ</p>
        </div>
    </div>

    <a href="{{ route('guide.departures.show', $departure->id) }}" class="btn btn-secondary">Quay lại chi tiết tour</a>
</div>
@endsection
