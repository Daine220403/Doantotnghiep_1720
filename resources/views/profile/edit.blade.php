@php
    $title = 'Chỉnh sửa thông tin hồ sơ - Vie Travel';
@endphp

@extends('layouts.app-guest')

@section('content')
    <section class="pt-32 pb-16 bg-gray-50 min-h-screen">
        <div class="max-w-screen-xl mx-auto px-4">
            {{-- HEADER --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
                <div>
                    <p class="text-sm font-medium text-sky-600 mb-1">Quản lý tài khoản</p>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
                        Chỉnh sửa thông tin cá nhân
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Cập nhật thông tin hồ sơ và quản lý cài đặt tài khoản của bạn.
                    </p>
                </div>

                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:ring-4 focus:ring-gray-200">
                    ← Quay lại Dashboard
                </a>
            </div>

            <div class="grid gap-6 lg:grid-cols-3 mb-10">
                {{-- FORM SECTION --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        {{-- SUCCESS MESSAGE --}}
                        @if (session('status') === 'profile-updated')
                            <div class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-emerald-900">Cập nhật thành công!</p>
                                        <p class="text-sm text-emerald-700 mt-1">Thông tin hồ sơ của bạn đã được cập nhật.</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- FORM --}}
                        <form method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            @method('PATCH')

                            <div class="space-y-6">
                                {{-- NAME FIELD --}}
                                <div>
                                    <label for="name" class="block text-sm font-semibold text-gray-900 mb-2">
                                        Họ và tên
                                    </label>
                                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                                        class="w-full px-4 py-2 rounded-lg border @error('name') border-red-500 @else border-gray-300 @enderror bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                        placeholder="Nhập họ và tên" />
                                    @error('name')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- EMAIL FIELD --}}
                                <div>
                                    <label for="email" class="block text-sm font-semibold text-gray-900 mb-2">
                                        Email
                                    </label>
                                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                                        class="w-full px-4 py-2 rounded-lg border @error('email') border-red-500 @else border-gray-300 @enderror bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                        placeholder="Nhập email" />
                                    @error('email')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    {{-- @if ($user->email_verified_at === null)
                                        <p class="mt-2 text-sm text-amber-600">
                                            ⚠️ Email của bạn chưa được xác minh. Kiểm tra email của bạn để xác minh.
                                        </p>
                                    @endif --}}
                                </div>

                                {{-- READ-ONLY FIELDS --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                                            Ngày tham gia
                                        </label>
                                        <input type="text" disabled value="{{ optional($user->created_at)->format('d/m/Y H:i') }}"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-gray-100 text-gray-600" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                                            Trạng thái tài khoản
                                        </label>
                                        <input type="text" disabled
                                            value="{{ $user->status === 'active' ? 'Hoạt động' : 'Đang xử lý' }}"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-gray-100 text-gray-600" />
                                    </div>
                                </div>

                                @if ($user->phone)
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                                            Số điện thoại
                                        </label>
                                        <input type="text" disabled value="{{ $user->phone }}"
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-gray-100 text-gray-600" />
                                    </div>
                                @endif

                                {{-- ACTION BUTTONS --}}
                                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                                    <button type="submit"
                                        class="inline-flex items-center gap-2 px-6 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 transition">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.776-1 4.5 4.5 0 11.9.5H15a1 1 0 000-2h-.5a4 4 0 100-8A4 4 0 009 1a4.5 4.5 0 11.5 9 1.5 1.5 0 01-.5 3v.5a1 1 0 001 1 1 1 0 100 2h-1a1 1 0 01-1-1 3 3 0 00-3-3h-.5z" />
                                        </svg>
                                        Lưu thay đổi
                                    </button>

                                    <a href="{{ route('dashboard') }}"
                                        class="inline-flex items-center gap-2 px-6 py-2 rounded-lg border border-gray-300 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:ring-4 focus:ring-gray-200 transition">
                                        Hủy
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- PASSWORD CHANGE FORM --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mt-6">
                        {{-- SUCCESS MESSAGE --}}
                        @if (session('status') === 'password-updated')
                            <div class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-emerald-900">Cập nhật thành công!</p>
                                        <p class="text-sm text-emerald-700 mt-1">Mật khẩu của bạn đã được thay đổi.</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Thay đổi mật khẩu</h3>

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            {{--method patch dùng để  --}}
                            @method('PATCH')
                            <div class="space-y-6">
                                {{-- CURRENT PASSWORD --}}
                                <div>
                                    <label for="current_password" class="block text-sm font-semibold text-gray-900 mb-2">
                                        Mật khẩu hiện tại
                                    </label>
                                    <input type="password" id="current_password" name="current_password"
                                        class="w-full px-4 py-2 rounded-lg border @error('current_password') border-red-500 @else border-gray-300 @enderror bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                        placeholder="Nhập mật khẩu hiện tại" />
                                    @error('current_password')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- NEW PASSWORD --}}
                                <div>
                                    <label for="password" class="block text-sm font-semibold text-gray-900 mb-2">
                                        Mật khẩu mới
                                    </label>
                                    <input type="password" id="password" name="password"
                                        class="w-full px-4 py-2 rounded-lg border @error('password') border-red-500 @else border-gray-300 @enderror bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                        placeholder="Nhập mật khẩu mới" />
                                    @error('password')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-2 text-xs text-gray-500">Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.</p>
                                </div>

                                {{-- CONFIRM PASSWORD --}}
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-900 mb-2">
                                        Xác nhận mật khẩu mới
                                    </label>
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                        class="w-full px-4 py-2 rounded-lg border @error('password_confirmation') border-red-500 @else border-gray-300 @enderror bg-white text-gray-900 placeholder-gray-500 focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                        placeholder="Xác nhận mật khẩu mới" />
                                    @error('password_confirmation')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- ACTION BUTTONS --}}
                                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                                    <button type="submit"
                                        class="inline-flex items-center gap-2 px-6 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 transition">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.776-1 4.5 4.5 0 11.9.5H15a1 1 0 000-2h-.5a4 4 0 100-8A4 4 0 009 1a4.5 4.5 0 11.5 9 1.5 1.5 0 01-.5 3v.5a1 1 0 001 1 1 1 0 100 2h-1a1 1 0 01-1-1 3 3 0 00-3-3h-.5z" />
                                        </svg>
                                        Lưu thay đổi
                                    </button>

                                    <a href="{{ route('dashboard') }}"
                                        class="inline-flex items-center gap-2 px-6 py-2 rounded-lg border border-gray-300 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:ring-4 focus:ring-gray-200 transition">
                                        Hủy
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- SIDEBAR --}}
                <div>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Hướng dẫn</h3>

                        <div class="space-y-4 text-sm text-gray-600">
                            <div>
                                <p class="font-semibold text-gray-900 mb-1">📝 Thông tin cá nhân</p>
                                <p>Cập nhật họ tên và email của bạn. Email được dùng để đăng nhập.</p>
                            </div>

                            <div>
                                <p class="font-semibold text-gray-900 mb-1">✓ Xác minh Email</p>
                                <p>Hãy xác minh email để bảo vệ tài khoản của bạn.</p>
                            </div>

                            <div>
                                <p class="font-semibold text-gray-900 mb-1">🔒 Bảo mật</p>
                                <p>Thay đổi mật khẩu của bạn thường xuyên để bảo vệ tài khoản.</p>
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <a href="{{ route('dashboard') }}"
                                class="inline-flex items-center gap-2 text-sm text-sky-600 font-semibold hover:underline">
                                ← Quay lại Dashboard
                            </a>
                        </div>
                    </div>

                    {{-- INFO CARD --}}
                    <div class="mt-4 bg-blue-50 rounded-2xl border border-blue-200 p-6">
                        <p class="text-sm text-blue-900">
                            <span class="font-semibold">💡 Mẹo:</span> Cập nhật hồ sơ của bạn để nhận được trải nghiệm tốt nhất khi sử dụng VieTravel.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
