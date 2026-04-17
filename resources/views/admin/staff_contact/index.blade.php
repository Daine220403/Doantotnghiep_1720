@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">Danh sách liên hệ</h1>
                <p class="mb-0 text-muted">Nhân viên theo dõi và lọc các yêu cầu liên hệ từ khách hàng.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('info'))
            <div class="alert alert-info">
                {{ session('info') }}
            </div>
        @endif

        <div class="row mb-3">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body py-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng liên hệ</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body py-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Mới</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['new']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body py-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Đang xử lý</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['processing']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body py-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Đã xử lý</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['resolved']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.staff-contacts.index') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="small text-muted mb-1" for="q">Từ khóa</label>
                            <input type="text" class="form-control" id="q" name="q" value="{{ request('q') }}"
                                placeholder="Tên, email, SĐT, chủ đề hoặc nội dung">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="small text-muted mb-1" for="status">Trạng thái</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">Tất cả</option>
                                <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>Mới</option>
                                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Đã xử lý</option>
                            </select>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="small text-muted mb-1" for="preferred_contact">Kênh liên hệ</label>
                            <select class="form-control" id="preferred_contact" name="preferred_contact">
                                <option value="">Tất cả</option>
                                <option value="phone" {{ request('preferred_contact') === 'phone' ? 'selected' : '' }}>Điện thoại</option>
                                <option value="email" {{ request('preferred_contact') === 'email' ? 'selected' : '' }}>Email</option>
                                <option value="zalo" {{ request('preferred_contact') === 'zalo' ? 'selected' : '' }}>Zalo</option>
                            </select>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="small text-muted mb-1" for="date_from">Từ ngày</label>
                            <input type="date" class="form-control" id="date_from" name="date_from"
                                value="{{ request('date_from') }}">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="small text-muted mb-1" for="date_to">Đến ngày</label>
                            <input type="date" class="form-control" id="date_to" name="date_to"
                                value="{{ request('date_to') }}">
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <button type="submit" class="btn btn-primary btn-sm mr-2">Lọc</button>
                        <a href="{{ route('admin.staff-contacts.index') }}" class="btn btn-outline-secondary btn-sm">Xóa lọc</a>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger mt-3 mb-0">
                            {{ $errors->first() }}
                        </div>
                    @endif
                </form>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr class="text-center">
                                <th>#</th>
                                <th>Khách hàng</th>
                                <th>Chủ đề</th>
                                <th>Nội dung</th>
                                <th>Kênh ưu tiên</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                                <th>Ngày gửi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($messages as $index => $message)
                                @php
                                    $statusLabel = [
                                        'new' => 'Mới',
                                        'processing' => 'Đang xử lý',
                                        'resolved' => 'Đã xử lý',
                                    ][$message->status] ?? $message->status;

                                    $statusClass = [
                                        'new' => 'danger',
                                        'processing' => 'warning',
                                        'resolved' => 'success',
                                    ][$message->status] ?? 'secondary';

                                    $preferredContactLabel = [
                                        'phone' => 'Điện thoại',
                                        'email' => 'Email',
                                        'zalo' => 'Zalo',
                                    ][$message->preferred_contact] ?? '-';
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $messages->firstItem() + $index }}</td>
                                    <td>
                                        <div class="font-weight-bold">{{ $message->name }}</div>
                                        <div class="small text-muted">{{ $message->email }}</div>
                                        <div class="small text-muted">{{ $message->phone ?: '-' }}</div>
                                    </td>
                                    <td>{{ $message->subject }}</td>
                                    <td title="{{ $message->message }}">{{ \Illuminate\Support\Str::limit($message->message, 90) }}</td>
                                    <td class="text-center">{{ $preferredContactLabel }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $statusClass }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($message->status !== 'resolved')
                                            <form method="POST"
                                                action="{{ route('admin.staff-contacts.resolve', $message) }}"
                                                onsubmit="return confirm('Xác nhận đánh dấu liên hệ này là đã xử lý?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-success btn-sm">Đã xử lý</button>
                                            </form>
                                        @else
                                            <span class="text-muted small">Đã hoàn tất</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ optional($message->created_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Không có liên hệ nào phù hợp bộ lọc.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $messages->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
