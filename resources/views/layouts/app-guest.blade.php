<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="{{ asset('storage/image/logo_tron.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>{{ $title }}</title>
</head>

<body class="bg-white text-gray-900">
    <div class="container mx-auto">
        <!-- NAVBAR -->
        <nav class="bg-white/90 backdrop-blur border-b border-gray-200 fixed top-0 left-0 right-0 z-50">
            <div class="flex flex-wrap items-center justify-between max-w-screen-xl mx-auto px-4 py-2">
                <a href="#" class="flex items-center space-x-3">
                    <img src="{{ asset('storage/image/logo.png') }}" class="h-16" alt="VieTravel Logo" />
                    <span class="text-2xl font-bold text-gray-900">VieTravel</span>
                </a>

                <div class="flex items-center md:order-2 space-x-2">
                    @if (auth()->check() && auth()->user()->role == 'customer')
                        <div type="button" data-dropdown-toggle="userDropdown" data-dropdown-placement="bottom-start"
                            class="flex items-center gap-2 cursor-pointer">
                            <img id="avatarButton" class="w-10 h-10 rounded-full cursor-pointer"
                                src="{{ asset('storage/image/logo.png') }}" alt="User dropdown">

                            {{-- tên {{ auth()->user()->name }} --}}
                            <span class="font-medium text-gray-700">{{ auth()->user()->name }}</span>
                        </div>

                        <!-- Dropdown menu -->
                        <div id="userDropdown"
                            class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44">

                            <!-- User info -->
                            <div class="px-4 py-3 text-sm text-gray-900">
                                <div class="font-semibold">{{ auth()->user()->name }}</div>
                                <div class="truncate text-gray-500">{{ auth()->user()->email }}</div>
                            </div>

                            <!-- Menu -->
                            <ul class="py-2 text-sm text-gray-700" aria-labelledby="avatarButton">
                                <li>
                                    <a href="#" class="block px-4 py-2 hover:bg-gray-100 transition">
                                        Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="block px-4 py-2 hover:bg-gray-100 transition">
                                        Đơn đặt tour
                                    </a>
                                </li>
                            </ul>

                            <!-- Logout -->
                            <div class="py-1">
                                <a href="{{ route('logout') }}"
                                    class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                    Đăng xuất
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="">
                            <a href="{{ route('signin') }}"
                                class="text-gray-700 hover:bg-gray-50 font-medium rounded-lg text-sm px-4 py-2 focus:ring-4 focus:ring-gray-200">
                                Đăng nhập
                            </a>
                            <a href="{{ route('signup') }}"
                                class="text-white bg-blue-600 hover:bg-blue-700 font-medium rounded-lg text-sm px-4 py-2 focus:ring-4 focus:ring-blue-200">
                                Đăng ký
                            </a>
                        </div>
                    @endif



                    <button data-collapse-toggle="mega-menu" type="button"
                        class="inline-flex items-center justify-center w-10 h-10 text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:ring-2 focus:ring-gray-200"
                        aria-controls="mega-menu" aria-expanded="false">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M1 1h15M1 7h15M1 13h15" />
                        </svg>
                    </button>
                </div>


                <div id="mega-menu" class="hidden w-full md:flex md:w-auto md:order-1">
                    <ul class="flex flex-col md:flex-row md:space-x-8 font-medium mt-4 md:mt-0">
                        <li>
                            <a href="{{ route('home') }}"
                                class="block py-2 transition
                                {{ request()->routeIs('home')
                                    ? 'text-sky-600 md:border-b-2 md:border-sky-500 font-semibold'
                                    : 'text-gray-900 md:border-b-2 md:border-transparent md:hover:border-sky-500 md:hover:text-sky-500' }}">
                                Trang chủ
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tours') }}"
                                class="block py-2 transition
                                    {{ request()->is('tours*')
                                        ? 'text-sky-600 md:border-b-2 md:border-sky-500 font-semibold'
                                        : 'text-gray-900 md:border-b-2 md:border-transparent md:hover:border-sky-500 md:hover:text-sky-500' }}">
                                Tours
                            </a>

                        </li>

                        <li>
                            <a href="#"
                                class="block py-2 text-gray-900 md:border-b-2 md:border-transparent md:hover:border-sky-500 md:hover:text-sky-500 transition">
                                Liên hệ
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        @yield('content')



        <!-- FOOTER -->
        <footer class="bg-gradient-to-b from-sky-900 to-blue-950 text-sky-100">
            <div class="max-w-screen-xl mx-auto px-4 py-14">

                <!-- TOP -->
                <div class="grid gap-10 md:grid-cols-4">

                    <!-- BRAND -->
                    <div>
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('storage/image/logo.png') }}" class="h-12 bg-white rounded p-1"
                                alt="VieTravel Logo">
                            <span class="text-xl font-bold text-white">VieTravel</span>
                        </div>
                        <p class="mt-4 text-sm text-sky-200 leading-relaxed">
                            VieTravel – Nền tảng đặt tour du lịch trong nước & quốc tế,
                            mang đến trải nghiệm an toàn, tiết kiệm và đáng nhớ cho mọi hành trình.
                        </p>
                    </div>

                    <!-- COMPANY -->
                    <div>
                        <h3 class="text-white font-semibold mb-4">Về chúng tôi</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-white transition">Giới thiệu</a></li>
                            <li><a href="#" class="hover:text-white transition">Điều khoản sử dụng</a></li>
                            <li><a href="#" class="hover:text-white transition">Chính sách bảo mật</a></li>
                            <li><a href="#" class="hover:text-white transition">Tuyển dụng</a></li>
                        </ul>
                    </div>

                    <!-- TOURS -->
                    <div>
                        <h3 class="text-white font-semibold mb-4">Danh mục tour</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-white transition">Tour trong nước</a></li>
                            <li><a href="#" class="hover:text-white transition">Tour quốc tế</a></li>
                            <li><a href="#" class="hover:text-white transition">Tour khuyến mãi</a></li>
                            <li><a href="#" class="hover:text-white transition">Tour cao cấp</a></li>
                        </ul>
                    </div>

                    <!-- CONTACT -->
                    <div>
                        <h3 class="text-white font-semibold mb-4">Liên hệ</h3>
                        <ul class="space-y-3 text-sm text-sky-200">
                            <li class="flex gap-2">📍 12 Nguyễn Văn Bảo, Q.Gò Vấp, TP.HCM</li>
                            <li class="flex gap-2">📞 1900 1234</li>
                            <li class="flex gap-2">✉️ support@vietravel.vn</li>
                        </ul>

                        <!-- SOCIAL -->
                        <div class="flex gap-3 mt-4">
                            <a href="#"
                                class="w-9 h-9 flex items-center justify-center rounded-full bg-sky-800 hover:bg-sky-600 transition">
                                f
                            </a>
                            <a href="#"
                                class="w-9 h-9 flex items-center justify-center rounded-full bg-sky-800 hover:bg-sky-600 transition">
                                in
                            </a>
                            <a href="#"
                                class="w-9 h-9 flex items-center justify-center rounded-full bg-sky-800 hover:bg-sky-600 transition">
                                yt
                            </a>
                        </div>
                    </div>

                </div>

                <!-- DIVIDER -->
                <div
                    class="border-t border-sky-800 mt-10 pt-6 flex flex-col md:flex-row items-center justify-between gap-4">
                    <p class="text-sm text-sky-300">
                        © {{ date('Y') }} VieTravel. All rights reserved.
                    </p>

                    <div class="flex gap-4 text-sm text-sky-300">
                        <a href="#" class="hover:text-white transition">Điều khoản</a>
                        <a href="#" class="hover:text-white transition">Bảo mật</a>
                        <a href="#" class="hover:text-white transition">Liên hệ</a>
                    </div>
                </div>

            </div>
        </footer>
        <!-- CHATBOT WIDGET -->
        <div class="fixed bottom-5 right-5 z-[9999]">

            <!-- Panel -->
            <div id="chatbot-panel"
                class="hidden w-[360px] max-w-[92vw] rounded-2xl border border-gray-200 bg-white shadow-xl overflow-hidden">
                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-3 bg-gradient-to-r from-sky-500 to-blue-600">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center text-white font-bold">
                            VT
                        </div>
                        <div class="text-white">
                            <div class="font-semibold leading-tight">VieTravel Chatbot</div>
                            <div class="text-xs text-white/90">Hỗ trợ tư vấn tour 24/7</div>
                        </div>
                    </div>

                    <button id="chatbot-close"
                        class="w-9 h-9 rounded-full hover:bg-white/15 text-white flex items-center justify-center"
                        type="button" aria-label="Close chatbot">
                        ✕
                    </button>
                </div>

                <!-- Body -->
                <div id="chatbot-messages" class="h-80 overflow-y-auto px-4 py-4 space-y-3 bg-gray-50">
                    <!-- Bot welcome -->
                    <div class="flex items-start gap-2">
                        <div
                            class="w-8 h-8 rounded-full bg-sky-600 text-white flex items-center justify-center text-xs font-bold">
                            VT
                        </div>
                        <div
                            class="max-w-[80%] rounded-2xl rounded-tl-md bg-white border border-gray-200 px-3 py-2 text-sm text-gray-800">
                            Chào anh 👋 Em là trợ lý VieTravel. Anh muốn tìm tour <b>trong nước</b> hay <b>quốc tế</b>
                            ạ?
                        </div>
                    </div>
                </div>

                <!-- Quick replies -->
                <div class="px-4 py-2 bg-white border-t border-gray-200">
                    <div class="flex flex-wrap gap-2">
                        <button
                            class="chatbot-quick px-3 py-1.5 text-xs font-semibold rounded-full bg-sky-50 text-sky-700 hover:bg-sky-100"
                            type="button">Tour trong nước</button>
                        <button
                            class="chatbot-quick px-3 py-1.5 text-xs font-semibold rounded-full bg-sky-50 text-sky-700 hover:bg-sky-100"
                            type="button">Tour quốc tế</button>
                        <button
                            class="chatbot-quick px-3 py-1.5 text-xs font-semibold rounded-full bg-sky-50 text-sky-700 hover:bg-sky-100"
                            type="button">Tư vấn giá</button>
                        <button
                            class="chatbot-quick px-3 py-1.5 text-xs font-semibold rounded-full bg-sky-50 text-sky-700 hover:bg-sky-100"
                            type="button">Liên hệ</button>
                    </div>
                </div>

                <!-- Input -->
                <form id="chatbot-form" class="flex items-center gap-2 px-3 py-3 bg-white border-t border-gray-200">
                    <input id="chatbot-input" type="text" placeholder="Nhập câu hỏi..."
                        class="flex-1 rounded-xl border border-gray-200 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-sky-100 focus:border-sky-500">
                    <button type="submit"
                        class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:ring-4 focus:ring-blue-200">
                        Gửi
                    </button>
                </form>
            </div>

            <!-- Floating button -->
            <button id="chatbot-toggle"
                class="mt-4 w-14 h-14 rounded-full bg-gradient-to-r from-sky-500 to-blue-600 shadow-lg hover:shadow-xl
               flex items-center justify-center text-white font-bold"
                type="button" aria-label="Open chatbot">
                💬
            </button>

        </div>

    </div>
    
</body>

</html>
