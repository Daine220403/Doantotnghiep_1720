@php($user = Auth::user())
@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Trang giới thiệu tài khoản</h1>
                <p class="mb-0 text-muted">
                    Xin chào, <span class="font-weight-bold">{{ $user->name ?? 'Admin' }}</span>
                    @if (!empty($user->role))
                        (vai trò: {{ $user->role }})
                    @endif
                </p>
            </div>
            <span class="d-none d-sm-inline-block text-muted">
                {{ now()->format('d/m/Y') }}
            </span>
        </div>

        <div class="row mb-4">
            <div class="col-lg-4 mb-3 mb-lg-0">
                <div class="card shadow h-100">
                    <div class="card-body d-flex flex-column align-items-center text-center">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mb-3"
                            style="width: 70px; height: 70px; font-size: 32px;">
                            {{ strtoupper(mb_substr($user->name ?? 'A', 0, 1, 'UTF-8')) }}
                        </div>
                        <h5 class="mb-1">{{ $user->name ?? 'Admin' }}</h5>
                        <p class="mb-1 text-muted small">{{ $user->email ?? '' }}</p>
                        @if (!empty($user->role))
                            <span class="badge badge-info text-uppercase mt-2">{{ $user->role }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Thông tin tài khoản</h6>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Họ tên</dt>
                            <dd class="col-sm-8">{{ $user->name ?? 'Admin' }}</dd>

                            <dt class="col-sm-4">Email</dt>
                            <dd class="col-sm-8">{{ $user->email ?? '-' }}</dd>

                            <dt class="col-sm-4">Vai trò</dt>
                            <dd class="col-sm-8">{{ $user->role ?? 'admin' }}</dd>

                            <dt class="col-sm-4">Ngày tạo tài khoản</dt>
                            <dd class="col-sm-8">{{ optional($user->created_at)->format('d/m/Y H:i') ?? '-' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
