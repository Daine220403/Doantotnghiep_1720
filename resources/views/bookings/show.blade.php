@php
    $title = 'Vie Travel - Hóa đơn đặt tour';
    $order = $booking->order;
    $departure = $booking->departure;
    $tour = $departure ? $departure->tour : null;
    $passengers = $booking->passengers;

    $statusLabel = [
        'pending' => 'Chờ xử lý',
        'confirmed' => 'Đã xác nhận',
        'paid' => 'Đã thanh toán',
        'cancelled' => 'Đã hủy',
        'completed' => 'Hoàn tất',
    ];

    $statusClass = [
        'pending' => 'bg-amber-50 text-amber-700',
        'confirmed' => 'bg-sky-50 text-sky-700',
        'paid' => 'bg-emerald-50 text-emerald-700',
        'cancelled' => 'bg-rose-50 text-rose-700',
        'completed' => 'bg-emerald-50 text-emerald-700',
    ];

    $currentStatus = $booking->status;

    $paidAmount = $paidAmount ?? 0;
    $remainingAmount = $remainingAmount ?? (($order->total_amount ?? 0) - $paidAmount);
    $isFullyPaid = $isFullyPaid ?? false;
    $isDeposit = $isDeposit ?? false;
    $lastPaymentType = $lastPaymentType ?? null;

    if ($currentStatus === 'confirmed' && $isDeposit) {
        $statusLabel['confirmed'] = 'Đã đặt cọc';
    }
@endphp

@extends('layouts.app-guest')

@section('content')
    <section class="pt-28 pb-16 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-xs font-semibold text-sky-600 uppercase tracking-wide mb-1">Hóa đơn đặt tour</p>
                    <h1 class="text-2xl font-bold text-gray-900">
                        #{{ $order->order_code ?? ('BK' . $booking->id) }}
                    </h1>
                    <p class="text-xs text-gray-500 mt-1">
                        Cảm ơn bạn đã tin tưởng Vie Travel. Đây là thông tin chi tiết đơn đặt tour của bạn.
                    </p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass[$currentStatus] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ $statusLabel[$currentStatus] ?? ucfirst($currentStatus) }}
                    </span>
                    <div class="mt-2 text-xs text-gray-500">
                        Ngày đặt: {{ optional($booking->created_at)->format('d/m/Y H:i') ?? '-' }}
                    </div>
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center gap-2 mt-3 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50">
                        Quay lại dashboard
                    </a>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2 mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Thông tin tour</h2>
                    @if ($tour)
                        <p class="text-sm font-semibold text-gray-900 mb-1">{{ $tour->title }}</p>
                        <p class="text-xs text-gray-500 mb-3">{{ $tour->destination_text }}</p>
                        <dl class="space-y-1 text-xs text-gray-600">
                            <div class="flex justify-between">
                                <dt>Mã tour</dt>
                                <dd class="font-medium text-gray-900">{{ $tour->code }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>Điểm khởi hành</dt>
                                <dd class="font-medium text-gray-900">{{ $tour->departure_location }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>Thời gian</dt>
                                <dd class="font-medium text-gray-900">{{ $tour->duration_days }}N{{ $tour->duration_nights }}Đ</dd>
                            </div>
                        </dl>
                    @else
                        <p class="text-xs text-gray-500">Tour đã bị ẩn hoặc không còn khả dụng.</p>
                    @endif
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Thông tin lịch khởi hành</h2>
                    @if ($departure)
                        <dl class="space-y-1 text-xs text-gray-600">
                            <div class="flex justify-between">
                                <dt>Ngày khởi hành</dt>
                                <dd class="font-medium text-gray-900">
                                    {{ \Carbon\Carbon::parse($departure->start_date)->format('d/m/Y') }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>Ngày kết thúc</dt>
                                <dd class="font-medium text-gray-900">
                                    {{ \Carbon\Carbon::parse($departure->end_date)->format('d/m/Y') }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>Điểm tập trung</dt>
                                <dd class="font-medium text-gray-900">{{ $departure->meeting_point }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>Số chỗ đã đặt</dt>
                                <dd class="font-medium text-gray-900">{{ $passengers->count() }} người</dd>
                            </div>
                        </dl>
                    @else
                        <p class="text-xs text-gray-500">Không tìm thấy thông tin lịch khởi hành.</p>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Thông tin liên hệ & thanh toán</h2>
                @if ($order)
                    <div class="grid gap-4 md:grid-cols-2 text-xs text-gray-600">
                        <dl class="space-y-1">
                            <div class="flex justify-between">
                                <dt>Mã đơn hàng</dt>
                                <dd class="font-medium text-gray-900">{{ $order->order_code }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>Tên liên hệ</dt>
                                <dd class="font-medium text-gray-900">{{ $order->contact_name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>Số điện thoại</dt>
                                <dd class="font-medium text-gray-900">{{ $order->contact_phone }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>Email</dt>
                                <dd class="font-medium text-gray-900 break-all">{{ $order->contact_email }}</dd>
                            </div>
                        </dl>
                        <dl class="space-y-1">
                            <div class="flex justify-between">
                                <dt>Trạng thái thanh toán</dt>
                                <dd class="font-medium text-gray-900">{{ $statusLabel[$currentStatus] ?? ucfirst($currentStatus) }}</dd>
                            </div>
                            @if ($lastPaymentType)
                                <div class="flex justify-between">
                                    <dt>Hình thức thanh toán</dt>
                                    <dd class="font-medium text-gray-900">
                                        @if ($lastPaymentType === 'deposit')
                                            Đặt cọc (thanh toán một phần)
                                        @else
                                            Thanh toán đủ
                                        @endif
                                    </dd>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <dt>Tổng tiền</dt>
                                <dd class="font-semibold text-emerald-600">
                                    {{ number_format($order->total_amount, 0, ',', '.') }} đ
                                </dd>
                            </div>
                            @if ($paidAmount > 0)
                                <div class="flex justify-between">
                                    <dt>Đã thanh toán</dt>
                                    <dd class="font-medium text-gray-900">
                                        {{ number_format($paidAmount, 0, ',', '.') }} đ
                                        @if ($order->total_amount > 0 && !$isFullyPaid)
                                            ({{ round($paidAmount / $order->total_amount * 100) }}%)
                                        @endif
                                    </dd>
                                </div>
                                @if ($remainingAmount > 0)
                                    <div class="flex justify-between">
                                        <dt>Còn lại</dt>
                                        <dd class="font-medium text-amber-700">
                                            {{ number_format($remainingAmount, 0, ',', '.') }} đ
                                        </dd>
                                    </div>
                                @endif
                            @endif
                            <div class="flex justify-between">
                                <dt>Ngày cập nhật</dt>
                                <dd class="font-medium text-gray-900">
                                    {{ optional($order->updated_at)->format('d/m/Y H:i') ?? '-' }}
                                </dd>
                            </div>

                            @if ($refundRequest)
                                <div class="border-t border-gray-100 pt-2 mt-2">
                                    <h3 class="font-semibold text-gray-900 text-xs mb-2">Thông tin yêu cầu hoàn tiền</h3>
                                    <div class="flex justify-between">
                                        <dt>Mã yêu cầu</dt>
                                        <dd class="font-medium text-gray-900">{{ $refundRequest->refund_code }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt>Trạng thái</dt>
                                        <dd class="font-medium text-gray-900">
                                            @switch($refundRequest->status)
                                                @case('pending')
                                                    <span class="text-amber-600">Chờ duyệt</span>
                                                @break
                                                @case('approved')
                                                    <span class="text-blue-600">Đã duyệt</span>
                                                @break
                                                @case('refunded')
                                                    <span class="text-emerald-600">Đã hoàn tiền</span>
                                                @break
                                                @case('rejected')
                                                    <span class="text-rose-600">Bị từ chối</span>
                                                @break
                                                @case('failed')
                                                    <span class="text-rose-600">Thất bại</span>
                                                @break
                                                @default
                                                    {{ ucfirst($refundRequest->status) }}
                                            @endswitch
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt>Số tiền hoàn</dt>
                                        <dd class="font-semibold text-emerald-600">
                                            {{ number_format($refundRequest->refund_amount, 0, ',', '.') }} đ
                                        </dd>
                                    </div>
                                    @if ($refundRequest->status === 'rejected' && $refundRequest->rejection_reason)
                                        <div class="flex justify-between mt-1">
                                            <dt>Lý do từ chối</dt>
                                            <dd class="font-medium text-rose-600 text-xs">
                                                {{ $refundRequest->rejection_reason }}
                                            </dd>
                                        </div>
                                    @endif
                                    @if ($refundRequest->status === 'failed' && is_array($refundRequest->vnpay_response) && isset($refundRequest->vnpay_response['error']))
                                        <div class="flex justify-between mt-1">
                                            <dt>Lỗi</dt>
                                            <dd class="font-medium text-rose-600 text-xs">
                                                {{ $refundRequest->vnpay_response['error'] }}
                                            </dd>
                                        </div>
                                    @endif
                                    @if ($refundRequest->status === 'refunded' && $refundRequest->refunded_at)
                                        <div class="flex justify-between">
                                            <dt>Ngày hoàn tiền</dt>
                                            <dd class="font-medium text-gray-900">
                                                {{ optional($refundRequest->refunded_at)->format('d/m/Y H:i') }}
                                            </dd>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </dl>
                    </div>
                @else
                    <p class="text-xs text-gray-500">Không có thông tin đơn hàng cho booking này.</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-900">Danh sách hành khách</h2>
                    <p class="text-xs text-gray-500">Tổng: {{ $passengers->count() }} người</p>
                </div>

                @if ($passengers->isEmpty())
                    <p class="text-xs text-gray-500">Chưa có thông tin hành khách.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead>
                                <tr class="text-left text-[11px] font-semibold text-gray-500 border-b border-gray-100">
                                    <th class="py-2 pr-4">Họ tên</th>
                                    <th class="py-2 pr-4">Loại khách</th>
                                    <th class="py-2 pr-4">Ngày sinh</th>
                                    <th class="py-2 pr-4">Giới tính</th>
                                    <th class="py-2 pr-4">Phụ thu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($passengers as $p)
                                    <tr>
                                        <td class="py-2 pr-4 text-gray-900">{{ $p->full_name }}</td>
                                        <td class="py-2 pr-4 text-gray-700">
                                            @switch($p->passenger_type)
                                                @case('adult')
                                                    Người lớn
                                                @break
                                                @case('child')
                                                    Trẻ em
                                                @break
                                                @case('infant')
                                                    Trẻ nhỏ
                                                @break
                                                @case('youth')
                                                    Em bé
                                                @break
                                                @default
                                                    Khác
                                            @endswitch
                                        </td>
                                        <td class="py-2 pr-4 text-gray-700">
                                            {{ $p->dob ? \Carbon\Carbon::parse($p->dob)->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="py-2 pr-4 text-gray-700">
                                            @if ($p->gender === 'male')
                                                Nam
                                            @elseif($p->gender === 'female')
                                                Nữ
                                            @else
                                                Khác
                                            @endif
                                        </td>
                                        <td class="py-2 pr-4 text-gray-700">
                                            @if ($p->single_room && ($p->single_room_surcharge ?? 0) > 0)
                                                {{ number_format($p->single_room_surcharge, 0, ',', '.') }} đ
                                            @else
                                                Không
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
