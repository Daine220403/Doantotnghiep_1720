@php
	$title = 'Vie Travel - Dashboard của tôi';
	$user = auth()->user();
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
							{{-- Sau này có thể truyền số đơn thực tế từ controller --}}
							<span>0</span>
						</p>
						<p class="text-xs text-gray-400 mt-1">Bạn chưa có đơn nào. Hãy bắt đầu hành trình mới!</p>
					</div>
				</div>

				<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
					<div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg">
						📅
					</div>
					<div>
						<p class="text-sm text-gray-500">Tour sắp khởi hành</p>
						<p class="text-2xl font-bold text-gray-900">
							<span>0</span>
						</p>
						<p class="text-xs text-gray-400 mt-1">Chưa có chuyến đi nào sắp diễn ra.</p>
					</div>
				</div>

				<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
					<div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-lg">
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
							<p class="text-xs text-gray-500 mt-1">Danh sách những chuyến đi bạn đã đặt và chuẩn bị khởi hành.</p>
						</div>
						<a href="{{ route('tours') }}" class="text-sm text-sky-600 font-semibold hover:underline">
							Khám phá thêm tour
						</a>
					</div>

					<div class="border border-dashed border-gray-200 rounded-xl p-6 text-center text-sm text-gray-500">
						<p class="mb-2">Hiện tại bạn chưa có tour nào sắp khởi hành.</p>
						<p class="mb-4">Hãy tìm kiếm một hành trình phù hợp cho kỳ nghỉ sắp tới.</p>
						<a href="{{ route('tours') }}"
							class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 focus:ring-4 focus:ring-blue-200">
							Bắt đầu đặt tour
						</a>
					</div>
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

			{{-- RECOMMENDATIONS --}}
			<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
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

