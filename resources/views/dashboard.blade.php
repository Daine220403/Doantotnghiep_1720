@php
    $title = 'Vie Travel - Dashboard của tôi';
@endphp

@extends('layouts.app-guest')

@section('content')
    <section class="pt-32 pb-16 bg-gray-50 min-h-screen">
        <div class="max-w-screen-xl mx-auto px-4">
            {{-- HEADER --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
                <div>
                    <p class="text-sm font-medium text-sky-600 mb-1">Xin chào,</p>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
                        {{ $user?->name ? 'Chào ' . $user->name : 'Dashboard khách hàng' }}
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Quản lý đơn đặt tour, thông tin tài khoản và hành trình của bạn tại một nơi.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('tours') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 focus:ring-4 focus:ring-blue-200">
                        <span>Đặt tour mới</span>
                    </a>
                    <a href="{{ route('profile.edit') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:ring-4 focus:ring-gray-200">
                        Cập nhật hồ sơ
                    </a>
                </div>
            </div>

            {{-- SUMMARY CARDS --}}
            <div class="grid gap-4 md:grid-cols-3 mb-10">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center text-lg">🏷️
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tổng đơn đặt tour</p>
                        <p class="text-2xl font-bold text-gray-900">
                            <span>{{ $totalOrders }}</span>
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            @if ($totalOrders > 0)
                                {{ $totalPaidOrders }} đơn đã thanh toán, tổng chi tiêu
                                {{ number_format($totalSpent, 0, ',', '.') }} đ
                            @else
                                Bạn chưa có đơn nào. Hãy bắt đầu hành trình mới!
                            @endif
                        </p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                    <div
                        class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg">
                        📅
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tour sắp khởi hành</p>
                        <p class="text-2xl font-bold text-gray-900">
                            <span>{{ $upcomingCount }}</span>
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            @if ($upcomingCount > 0)
                                Bạn có {{ $upcomingCount }} chuyến đi sắp diễn ra.
                            @else
                                Chưa có chuyến đi nào sắp diễn ra.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                    <div
                        class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-lg">
                        🎁
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Ưu đãi & điểm thưởng</p>
                        <p class="text-2xl font-bold text-gray-900">Đang cập nhật</p>
                        <p class="text-xs text-gray-400 mt-1">Theo dõi các chương trình khuyến mãi dành riêng cho bạn.</p>
                    </div>
                </div>
            </div>

            {{-- MAIN CONTENT --}}
            <div class="grid gap-6 lg:grid-cols-3 mb-10">
                {{-- UPCOMING TOURS --}}
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Tour sắp khởi hành</h2>
                            <p class="text-xs text-gray-500 mt-1">Danh sách những chuyến đi bạn đã đặt và chuẩn bị khởi
                                hành.</p>
                        </div>
                        <a href="{{ route('tours') }}" class="text-sm text-sky-600 font-semibold hover:underline">
                            Khám phá thêm tour
                        </a>
                    </div>

                    @if ($upcomingBookings->isEmpty())
                        <div class="border border-dashed border-gray-200 rounded-xl p-6 text-center text-sm text-gray-500">
                            <p class="mb-2">Hiện tại bạn chưa có tour nào sắp khởi hành.</p>
                            <p class="mb-4">Hãy tìm kiếm một hành trình phù hợp cho kỳ nghỉ sắp tới.</p>
                            <a href="{{ route('tours') }}"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 focus:ring-4 focus:ring-blue-200">
                                Bắt đầu đặt tour
                            </a>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-xs font-semibold text-gray-500 border-b border-gray-100">
                                        <th class="py-2 pr-4">Tour</th>
                                        <th class="py-2 pr-4">Ngày khởi hành</th>
                                        <th class="py-2 pr-4">Mã đơn</th>
                                        <th class="py-2 pr-4">Tổng tiền</th>
                                        <th class="py-2 pr-4">Đã trả</th>
                                        <th class="py-2 pr-4">Còn lại</th>
                                        <th class="py-2 pr-4">Đã cọc/Đủ</th>
                                        <th class="py-2 pr-4 text-right">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($upcomingBookings as $booking)
                                        @php
                                            $tour = optional($booking->departure)->tour;
                                            $order = $booking->order;
                                            $orderId = $order->id ?? null;
                                            $totalAmount = $order->total_amount ?? 0;
                                            $paidAmount = $orderId && isset($paidByOrder[$orderId]) ? $paidByOrder[$orderId] : 0;
                                            $remainingAmount = max($totalAmount - $paidAmount, 0);
                                            $isFullyPaid = $totalAmount > 0 && $paidAmount >= ($totalAmount - 1);
                                            $departureDate = optional($booking->departure)->start_date;
                                            $daysBeforeDeparture = $departureDate
                                                ? now()->diffInDays(\Carbon\Carbon::parse($departureDate), false)
                                                : null;
                                            $canModifyByDate = !is_null($daysBeforeDeparture) && $daysBeforeDeparture >= 7;
                                            $canCancel = $canModifyByDate && $booking->status !== 'cancelled';
                                            $canEdit = $canCancel && !$isFullyPaid;
                                        @endphp
                                        <tr>
                                            <td class="py-2 pr-4">
                                                <div class="font-semibold text-gray-900 line-clamp-1">
                                                    {{ $tour->title ?? 'Tour đã ẩn' }}</div>
                                                <div class="text-xs text-gray-500">{{ $tour->destination_text ?? '' }}
                                                </div>
                                            </td>
                                            <td class="py-2 pr-4 text-xs text-gray-700">
                                                {{ optional($booking->departure)->start_date ? \Carbon\Carbon::parse($booking->departure->start_date)->format('d/m/Y') : '-' }}
                                            </td>
                                            <td class="py-2 pr-4 text-xs text-gray-700">
                                                {{ $order->order_code ?? '-' }}
                                            </td>
                                            <td class="py-2 pr-4 text-xs text-gray-700">
                                                {{ $totalAmount > 0 ? number_format($totalAmount, 0, ',', '.') . ' đ' : '-' }}
                                            </td>
                                            <td class="py-2 pr-4 text-xs text-gray-700">
                                                {{ $paidAmount > 0 ? number_format($paidAmount, 0, ',', '.') . ' đ' : '0 đ' }}
                                            </td>
                                            <td class="py-2 pr-4 text-xs text-gray-700">
                                                {{ $remainingAmount > 0 ? number_format($remainingAmount, 0, ',', '.') . ' đ' : '0 đ' }}
                                            </td>
                                            <td class="py-2 pr-4 text-xs text-gray-700">
                                                @if ($paidAmount <= 0)
                                                    <span class="text-red-600 font-semibold">Chưa thanh toán</span>
                                                @elseif ($isFullyPaid)
                                                    <span class="text-emerald-600 font-semibold">Đã thanh toán đủ</span>
                                                @else
                                                    <span class="text-yellow-600 font-semibold">Đã đặt cọc</span>
                                                @endif
                                            </td>
                                            
                                            <td class="py-2 pl-4 pr-0 text-xs text-right">
                                                <div class="flex justify-end gap-2">
                                                    @if ($order && $remainingAmount > 0 && $booking->status !== 'cancelled')
                                                        <form action="{{ route('dashboard.bookings.pay', $booking->id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit"
                                                                class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-blue-600 text-white text-[11px] font-semibold hover:bg-blue-700">
                                                                {{ $paidAmount > 0 ? 'Thanh toán tiếp' : 'Thanh toán' }}
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if ($canEdit)
                                                        <a href="{{ route('dashboard.bookings.edit', $booking->id) }}"
                                                            class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-amber-300 bg-amber-50 text-[11px] font-semibold text-amber-700 hover:bg-amber-100">
                                                            Sửa thông tin
                                                        </a>
                                                    @endif

                                                    @if ($canCancel)
                                                        <form action="{{ route('dashboard.bookings.cancel', $booking->id) }}" method="POST"
                                                            onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn này?');">
                                                            @csrf
                                                            <button type="submit"
                                                                class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-rose-200 bg-rose-50 text-[11px] font-semibold text-rose-700 hover:bg-rose-100">
                                                                Hủy
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <a href="{{ route('dashboard.bookings.show', $booking->id) }}"
                                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-gray-200 bg-white text-[11px] font-semibold text-gray-700 hover:bg-gray-50">
                                                        Xem hóa đơn
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- ACCOUNT INFO --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Thông tin tài khoản</h2>

                    <dl class="space-y-3 text-sm">
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-gray-500">Họ và tên</dt>
                            <dd class="text-gray-900 font-medium text-right">
                                {{ $user?->name ?? 'Chưa cập nhật' }}
                            </dd>
                        </div>

                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-gray-500">Email</dt>
                            <dd class="text-gray-900 font-medium text-right break-all">
                                {{ $user?->email ?? 'Chưa cập nhật' }}
                            </dd>
                        </div>

                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-gray-500">Ngày tham gia</dt>
                            <dd class="text-gray-900 font-medium text-right">
                                {{ optional($user?->created_at)->format('d/m/Y') ?? 'Đang cập nhật' }}
                            </dd>
                        </div>

                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-gray-500">Trạng thái</dt>
                            <dd class="text-emerald-600 font-semibold text-right">
                                {{ $user?->status === 'active' ? 'Hoạt động' : 'Đang cập nhật' }}
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-5">
                        <a href="{{ route('profile.edit') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50 focus:ring-4 focus:ring-gray-200">
                            Chỉnh sửa thông tin
                        </a>
                    </div>
                </div>
            </div>
            {{-- BOOKED TOURS LIST --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Danh sách tour đã đặt</h2>
                        <p class="text-xs text-gray-500 mt-1">Tổng hợp các tour bạn đã đặt gần đây.</p>
                    </div>
                </div>

                @if ($recentBookings->isEmpty())
                    <div class="border border-dashed border-gray-200 rounded-xl p-6 text-center text-sm text-gray-500">
                        <p class="mb-2">Bạn chưa có lịch sử đặt tour.</p>
                        <p class="mb-4">Bắt đầu hành trình đầu tiên của bạn ngay hôm nay.</p>
                        <a href="{{ route('tours') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 focus:ring-4 focus:ring-blue-200">
                            Đặt tour ngay
                        </a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 border-b border-gray-100">
                                    <th class="py-2 pr-4">Tour</th>
                                    <th class="py-2 pr-4">Ngày khởi hành</th>
                                    <th class="py-2 pr-4">Mã đơn</th>
                                    <th class="py-2 pr-4">Tổng tiền</th>
                                    <th class="py-2 pr-4">Đã trả</th>
                                    <th class="py-2 pr-4">Còn lại</th>
                                    <th class="py-2 pr-4">Đã cọc/Đủ</th>
                                    <th class="py-2 pr-4">Ngày đặt</th>
                                    <th class="py-2 pr-4 text-right">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($recentBookings as $booking)
                                    @php
                                        $tour = optional($booking->departure)->tour;
                                        $order = $booking->order;
                                        $orderId = $order->id ?? null;
                                        $totalAmount = $order->total_amount ?? 0;
                                        $paidAmount = $orderId && isset($paidByOrder[$orderId]) ? $paidByOrder[$orderId] : 0;
                                        $remainingAmount = max($totalAmount - $paidAmount, 0);
                                        $isFullyPaid = $totalAmount > 0 && $paidAmount >= ($totalAmount - 1);
                                        $departureDate = optional($booking->departure)->start_date;
                                        $daysBeforeDeparture = $departureDate
                                            ? now()->diffInDays(\Carbon\Carbon::parse($departureDate), false)
                                            : null;
                                        $canModifyByDate = !is_null($daysBeforeDeparture) && $daysBeforeDeparture >= 7;
                                        $canCancel = $canModifyByDate && $booking->status !== 'cancelled';
                                        $canEdit = $canCancel && !$isFullyPaid;
                                    @endphp
                                    <tr>
                                        <td class="py-2 pr-4">
                                            <div class="font-semibold text-gray-900 line-clamp-1">
                                                {{ $tour->title ?? 'Tour đã ẩn' }}</div>
                                            <div class="text-xs text-gray-500">{{ $tour->destination_text ?? '' }}</div>
                                        </td>
                                        <td class="py-2 pr-4 text-xs text-gray-700">
                                            {{ optional($booking->departure)->start_date ? \Carbon\Carbon::parse($booking->departure->start_date)->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="py-2 pr-4 text-xs text-gray-700">
                                            {{ $order->order_code ?? '-' }}
                                        </td>
                                        <td class="py-2 pr-4 text-xs text-gray-700">
                                            {{ $totalAmount > 0 ? number_format($totalAmount, 0, ',', '.') . ' đ' : '-' }}
                                        </td>
                                        <td class="py-2 pr-4 text-xs text-gray-700">
                                            {{ $paidAmount > 0 ? number_format($paidAmount, 0, ',', '.') . ' đ' : '0 đ' }}
                                        </td>
                                        <td class="py-2 pr-4 text-xs text-gray-700">
                                            {{ $remainingAmount > 0 ? number_format($remainingAmount, 0, ',', '.') . ' đ' : '0 đ' }}
                                        </td>
                                        <td class="py-2 pr-4 text-xs text-gray-700">
                                            @if ($paidAmount <= 0)
                                                <span class="text-red-600 font-semibold">Chưa thanh toán</span>
                                            @elseif ($isFullyPaid)
                                                <span class="text-emerald-600 font-semibold">Đã thanh toán đủ</span>
                                            @else
                                                <span class="text-yellow-600 font-semibold">Đã đặt cọc</span>
                                            @endif
                                        </td>
                                        <td class="py-2 pr-4 text-xs text-gray-700">
                                            {{ optional($booking->created_at)->format('d/m/Y H:i') ?? '-' }}
                                        </td>
                                        <td class="py-2 pl-4 pr-0 text-xs text-right">
                                            <div class="flex justify-end gap-2">
                                                @if ($order && $remainingAmount > 0 && $booking->status !== 'cancelled')
                                                    <form action="{{ route('dashboard.bookings.pay', $booking->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit"
                                                            class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-blue-600 text-white text-[11px] font-semibold hover:bg-blue-700">
                                                            {{ $paidAmount > 0 ? 'Thanh toán tiếp' : 'Thanh toán' }}
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ($canEdit)
                                                    <a href="{{ route('dashboard.bookings.edit', $booking->id) }}"
                                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-amber-300 bg-amber-50 text-[11px] font-semibold text-amber-700 hover:bg-amber-100">
                                                        Sửa thông tin
                                                    </a>
                                                @endif

                                                @if ($canCancel)
                                                    <form action="{{ route('dashboard.bookings.cancel', $booking->id) }}" method="POST"
                                                        onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn này?');">
                                                        @csrf
                                                        <button type="submit"
                                                            class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-rose-200 bg-rose-50 text-[11px] font-semibold text-rose-700 hover:bg-rose-100">
                                                            Hủy
                                                        </button>
                                                    </form>
                                                @endif

                                                <a href="{{ route('dashboard.bookings.show', $booking->id) }}"
                                                    class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-gray-200 bg-white text-[11px] font-semibold text-gray-700 hover:bg-gray-50">
                                                    Xem hóa đơn
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            {{-- RECOMMENDATIONS --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Gợi ý dành cho bạn</h2>
                        <p class="text-xs text-gray-500 mt-1">Một số loại tour phổ biến mà khách hàng thường lựa chọn.</p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <a href="{{ route('tours', ['type' => 'domestic']) }}"
                        class="group rounded-2xl border border-gray-100 bg-gradient-to-br from-sky-50 to-white p-4 hover:border-sky-200 hover:shadow-sm transition">
                        <p class="text-xs font-semibold text-sky-600 mb-1">Trong nước</p>
                        <p class="text-sm font-semibold text-gray-900 mb-1">Tour nội địa nổi bật</p>
                        <p class="text-xs text-gray-500">Khám phá mọi miền Việt Nam với lịch trình linh hoạt.</p>
                    </a>

                    <a href="{{ route('tours', ['type' => 'international']) }}"
                        class="group rounded-2xl border border-gray-100 bg-gradient-to-br from-indigo-50 to-white p-4 hover:border-indigo-200 hover:shadow-sm transition">
                        <p class="text-xs font-semibold text-indigo-600 mb-1">Quốc tế</p>
                        <p class="text-sm font-semibold text-gray-900 mb-1">Tour nước ngoài hot</p>
                        <p class="text-xs text-gray-500">Trải nghiệm văn hoá và ẩm thực tại các quốc gia khác.</p>
                    </a>

                    <a href="{{ route('tours') }}"
                        class="group rounded-2xl border border-gray-100 bg-gradient-to-br from-amber-50 to-white p-4 hover:border-amber-200 hover:shadow-sm transition">
                        <p class="text-xs font-semibold text-amber-600 mb-1">Ưu đãi</p>
                        <p class="text-sm font-semibold text-gray-900 mb-1">Tour đang khuyến mãi</p>
                        <p class="text-xs text-gray-500">Săn các chương trình giảm giá và combo hấp dẫn.</p>
                    </a>
                </div>
            </div>


        </div>
    </section>
@endsection
