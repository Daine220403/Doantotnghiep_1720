@php
    $title = 'Liên hệ - Vie Travel';
@endphp
@extends('layouts.app-guest')
@section('content')
    <section class="pt-24 pb-14 py-14 bg-slate-50">
        <div class="max-w-screen-xl mx-auto px-4 grid lg:grid-cols-12 gap-8">
            <div class="lg:col-span-7">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 md:p-8">
                    <h2 class="text-2xl font-bold text-slate-900">Gửi yêu cầu tư vấn</h2>
                    <p class="text-slate-600 mt-2">Điền thông tin, đội ngũ VieTravel sẽ liên hệ và đề xuất hành trình phù
                        hợp.</p>

                    @if (session('success'))
                        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                            <ul class="list-disc pl-5 space-y-1 text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('contact.store') }}" method="POST"
                        class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Họ và tên *</label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Nguyễn Văn A"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none focus:ring-4 focus:ring-cyan-100 focus:border-cyan-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Email *</label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none focus:ring-4 focus:ring-cyan-100 focus:border-cyan-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Số điện thoại</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="0901234567"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none focus:ring-4 focus:ring-cyan-100 focus:border-cyan-500">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Kênh liên hệ ưu tiên</label>
                            <select name="preferred_contact"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none focus:ring-4 focus:ring-cyan-100 focus:border-cyan-500">
                                <option value="">Chọn kênh liên hệ</option>
                                <option value="phone" @selected(old('preferred_contact') === 'phone')>Điện thoại</option>
                                <option value="email" @selected(old('preferred_contact') === 'email')>Email</option>
                                <option value="zalo" @selected(old('preferred_contact') === 'zalo')>Zalo</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Chủ đề *</label>
                            <input type="text" name="subject" value="{{ old('subject') }}"
                                placeholder="Ví dụ: Tư vấn tour Đà Nẵng 4N3Đ"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none focus:ring-4 focus:ring-cyan-100 focus:border-cyan-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nội dung *</label>
                            <textarea name="message" rows="6" placeholder="Mô tả nhu cầu của bạn (số người, ngân sách, thời gian đi...)"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none focus:ring-4 focus:ring-cyan-100 focus:border-cyan-500">{{ old('message') }}</textarea>
                        </div>

                        <div class="md:col-span-2 flex flex-wrap items-center gap-3 pt-2">
                            <button type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-cyan-600 px-6 py-3 text-white font-semibold hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-200 transition">
                                Gửi yêu cầu
                            </button>
                            <span class="text-sm text-slate-500">Bằng việc gửi biểu mẫu, bạn đồng ý để VieTravel liên hệ tư
                                vấn.</span>
                        </div>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-5 space-y-6">
                <div class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
                    <h3 class="text-xl font-bold text-slate-900">Thông tin văn phòng</h3>
                    <div class="mt-4 space-y-3 text-slate-700">
                        <p><span class="font-semibold">Địa chỉ:</span> 12 Nguyễn Văn Bảo, Gò Vấp, TP.HCM</p>
                        <p><span class="font-semibold">Hotline:</span> 1900 xxxx</p>
                        <p><span class="font-semibold">Email:</span> support@vietravel.vn</p>
                        <p><span class="font-semibold">Giờ làm việc:</span> 08:00 - 21:00 (Thứ 2 - Thứ 6)</p>
                    </div>
                </div>

                <div class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
                    <h3 class="text-xl font-bold text-slate-900">Quy trình hỗ trợ</h3>
                    <ol class="mt-4 space-y-3 text-sm text-slate-700 list-decimal pl-5">
                        <li>Tiếp nhận thông tin và xác nhận nhu cầu.</li>
                        <li>Đề xuất hành trình và chi phí phù hợp.</li>
                        <li>Hỗ trợ đặt tour và theo dõi trước ngày khởi hành.</li>
                    </ol>
                </div>

                <div class="rounded-3xl bg-gradient-to-br from-cyan-500 to-blue-600 text-white p-6 shadow-md">
                    <h3 class="text-xl font-bold">Cần hỗ trợ gấp?</h3>
                    <p class="mt-2 text-cyan-50">Gọi hotline để được tư vấn nhanh và giữ chỗ sớm cho lịch khởi hành gần
                        nhất.</p>
                    <a href="tel:19001234"
                        class="mt-4 inline-flex items-center rounded-xl bg-white text-blue-700 font-bold px-4 py-2 hover:bg-blue-50 transition">
                        Gọi ngay 1900 xxxx
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
