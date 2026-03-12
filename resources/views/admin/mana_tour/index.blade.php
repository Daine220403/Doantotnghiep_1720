@extends('admin.layout.app')
<script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
<style>
    #dataTable tbody td {
        vertical-align: middle !important;
    }

    #dataTable tbody td div {
        margin-bottom: 0 !important;
    }
</style>
@section('content')
    <div class="container-fluid">

        {{-- Page Heading --}}
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Quản lý tour</h1>
                <p class="mb-0 text-muted">
                    Danh sách các tour hiện có. Bạn có thể thêm, sửa, xem chi tiết hoặc xóa tour để quản lý hiệu quả.
                </p>
            </div>

            {{-- Nút Thêm tour mới --}}
            <a href="{{ route('admin.mana-tour.create') }}" class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Thêm tour mới
            </a>
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

        {{-- Card --}}
        <div class="card shadow mb-4">

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm text-center align-middle" id="dataTable"
                        width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th>ID</th>
                                <th style="width: 55px; height: 38px; object-fit: cover; border-radius: 4px;">Ảnh</th>
                                <th>Tên tour</th>
                                <th>Loại</th>
                                <th>Điểm đi</th>
                                <th>Điểm đến</th>
                                <th>Thời gian</th>
                                <th>Giá</th>
                                <th>Trạng thái</th>
                                <th style="width: 120px;">Hành động</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                $index = 1;
                            @endphp
                            @foreach ($tours as $tour)
                                <tr>
                                    <td class="text-center">{{ $index++ }}</td>

                                    <td class="text-center">
                                        <img src="{{ asset('storage/' . $tour->images->first()->url ?? 'https://via.placeholder.com/70x50') }}"
                                            alt="{{ $tour->title }}"
                                            style="width: 70px; height: 50px; object-fit: cover; border-radius: 6px;">
                                    </td>

                                    <td>
                                        <div class="font-weight-bold text-gray-800">{{ $tour->title }}</div>
                                        <div class="small text-muted">Slug: {{ $tour->slug }}</div>
                                    </td>

                                    <td>
                                        @if ($tour->tour_type == 'domestic')
                                            <span class="badge badge-info">Trong nước</span>
                                        @elseif ($tour->tour_type == 'international')
                                            <span class="badge badge-primary">Quốc tế</span>
                                        @endif
                                    </td>

                                    <td>{{ $tour->departure_location }}</td>
                                    <td>{{ $tour->destination_text }}</td>

                                    <td>{{ $tour->duration_days }}N{{ $tour->duration_nights }}Đ</td>

                                    <td class="text-right">{{ number_format($tour->base_price_from, 0, ',', '.') }} đ</td>

                                    <td class="text-center">
                                        @if ($tour->status == 'draft')
                                            <span class="badge badge-secondary">Nháp</span>
                                        @elseif ($tour->status == 'published')
                                            <span class="badge badge-success">Đang bán</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <!-- Button modal xem chi tiết -->
                                        <button type="button" class="btn btn-sm btn-warning" data-toggle="modal"
                                            data-target="#ModalXemChiTiet{{ $tour->id }}" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal"
                                            data-target="#ModalSua{{ $tour->id }}" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <form action="#" method="POST" class="d-inline"
                                            onsubmit="return confirm('Bạn chắc chắn muốn xóa tour: Tour Đà Lạt 3N2Đ ?');">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>

                    </table>
                </div>
            </div>
        </div>

    </div>
    @include('admin.mana_tour.listModal')
@endsection
