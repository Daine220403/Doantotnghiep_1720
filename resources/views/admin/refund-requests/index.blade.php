@php
    $title = 'Quản lý yêu cầu hoàn tiền';
@endphp

@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">

        <!-- Page Heading -->
        <h1 class="h3 mb-2 text-gray-800">{{ $title }}</h1>
        <p class="mb-4">Xem và xử lý các yêu cầu hoàn tiền từ khách hàng</p>

        <!-- Stats -->
        <div class="row">

            <div class="col-xl col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Chờ duyệt</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_count'] }}</div>
                        <div class="text-xs text-muted">{{ number_format($stats['pending'], 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>

            <div class="col-xl col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Đã duyệt</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $stats['approved'] > 0 ? floor($stats['approved'] / 1000) : 0 }}
                        </div>
                        <div class="text-xs text-muted">{{ number_format($stats['approved'], 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>

            <div class="col-xl col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Đã hoàn tiền</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['refunded_count'] }}</div>
                        <div class="text-xs text-muted">{{ number_format($stats['refunded'], 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>

            <div class="col-xl col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Từ chối</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $stats['rejected'] > 0 ? floor($stats['rejected'] / 1000) : 0 }}
                        </div>
                        <div class="text-xs text-muted">{{ number_format($stats['rejected'], 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>

            <div class="col-xl col-md-6 mb-4">
                <div class="card border-left-secondary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Tổng</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ number_format($stats['total'], 0, ',', '.') }} đ
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Filter -->
        <div class="mb-3">
            <a href="{{ route('admin.refund-requests.index', ['status' => 'all']) }}"
                class="btn btn-sm {{ $status === 'all' ? 'btn-primary' : 'btn-light' }}">Tất cả</a>

            <a href="{{ route('admin.refund-requests.index', ['status' => 'pending']) }}"
                class="btn btn-sm {{ $status === 'pending' ? 'btn-warning' : 'btn-light' }}">Chờ duyệt</a>

            <a href="{{ route('admin.refund-requests.index', ['status' => 'approved']) }}"
                class="btn btn-sm {{ $status === 'approved' ? 'btn-primary' : 'btn-light' }}">Đã duyệt</a>

            <a href="{{ route('admin.refund-requests.index', ['status' => 'refunded']) }}"
                class="btn btn-sm {{ $status === 'refunded' ? 'btn-success' : 'btn-light' }}">Đã hoàn tiền</a>

            <a href="{{ route('admin.refund-requests.index', ['status' => 'rejected']) }}"
                class="btn btn-sm {{ $status === 'rejected' ? 'btn-danger' : 'btn-light' }}">Từ chối</a>

            <a href="{{ route('admin.refund-requests.index', ['status' => 'failed']) }}"
                class="btn btn-sm {{ $status === 'failed' ? 'btn-secondary' : 'btn-light' }}">Thất bại</a>
        </div>

        <!-- Table -->
        <div class="card shadow mb-4">
            <div class="card-body">

                @if ($refunds->count())
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%">
                            <thead>
                                <tr>
                                    <th>Mã hoàn tiền</th>
                                    <th>Khách hàng</th>
                                    <th>Tour</th>
                                    <th>Số tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày yêu cầu</th>
                                    <th class="text-right">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($refunds as $refund)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.refund-requests.show', $refund->id) }}">
                                                {{ $refund->refund_code }}
                                            </a>
                                        </td>

                                        <td>
                                            <strong>{{ $refund->user->name }}</strong><br>
                                            <small>{{ $refund->user->email }}</small>
                                        </td>

                                        <td>
                                            {{ $refund->booking->departure->tour->title ?? 'N/A' }}
                                        </td>

                                        <td>
                                            <strong>{{ number_format($refund->refund_amount, 0, ',', '.') }} đ</strong>
                                        </td>

                                        <td>
                                            <span class="badge {{ $refund->getStatusBadgeClass() }}">
                                                {{ $refund->getStatusLabel() }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ $refund->created_at->format('d/m/Y H:i') }}
                                        </td>

                                        <td class="text-right">
                                            <a href="{{ route('admin.refund-requests.show', $refund->id) }}"
                                                class="btn btn-sm btn-primary">
                                                Xem
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $refunds->links() }}
                    </div>
                @else
                    <div class="text-center text-muted">
                        Không có yêu cầu hoàn tiền nào
                    </div>
                @endif

            </div>
        </div>

    </div>
@endsection
