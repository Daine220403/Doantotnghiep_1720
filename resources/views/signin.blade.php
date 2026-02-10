@php
    $title = 'Vie Travel - Đăng nhập';
@endphp

@extends('layouts.guest')

@section('content')
<section class="pt-32 pb-20 bg-gray-50 min-h-screen">
    <div class="max-w-screen-xl mx-auto px-4">
        <div class="flex justify-center">
            <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-gray-200">

                <!-- Header -->
                <div class="px-6 py-6 border-b text-center">
                    <img src="{{ asset('storage/image/logo.png') }}" class="h-16 mx-auto mb-3" alt="VieTravel">
                    <h1 class="text-2xl font-bold text-gray-900">Đăng nhập VieTravel</h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Truy cập để đặt tour & quản lý hành trình của bạn
                    </p>
                </div>

                <!-- Form -->
                <div class="px-6 py-6">
                    <form method="POST" action="#">
                        @csrf

                        <!-- Email -->
                        <div class="mb-4">
                            <label class="block mb-1 text-sm font-medium text-gray-700">
                                Email
                            </label>
                            <input type="email" name="email" placeholder="example@email.com" required
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                       focus:ring-4 focus:ring-blue-100 focus:border-blue-500">
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label class="block mb-1 text-sm font-medium text-gray-700">
                                Mật khẩu
                            </label>
                            <input type="password" name="password" placeholder="••••••••" required
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                       focus:ring-4 focus:ring-blue-100 focus:border-blue-500">
                        </div>

                        <!-- Remember & Forgot -->
                        <div class="flex items-center justify-between mb-5">
                            <label class="flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox"
                                    class="w-4 h-4 rounded border-gray-300 focus:ring-blue-500">
                                Ghi nhớ đăng nhập
                            </label>
                            <a href="#" class="text-sm text-blue-600 hover:underline">
                                Quên mật khẩu?
                            </a>
                        </div>

                        <!-- Submit -->
                        <button type="submit"
                            class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-semibold
                                   hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 transition">
                            Đăng nhập
                        </button>
                    </form>

                    <!-- Divider -->
                    <div class="my-6 flex items-center gap-3">
                        <div class="flex-1 h-px bg-gray-200"></div>
                        <span class="text-sm text-gray-400">hoặc</span>
                        <div class="flex-1 h-px bg-gray-200"></div>
                    </div>

                    <!-- Social login -->
                    <button
                        class="w-full flex items-center justify-center gap-2 border border-gray-300 rounded-lg py-2.5
                               hover:bg-gray-50 transition text-sm font-medium">
                        <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5">
                        Đăng nhập bằng Google
                    </button>

                    <!-- Register -->
                    <p class="text-center text-sm text-gray-600 mt-6">
                        Chưa có tài khoản?
                        <a href="{{ route('signup') ?? '#' }}"
                            class="text-blue-600 font-semibold hover:underline">
                            Đăng ký ngay
                        </a>
                    </p>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection
