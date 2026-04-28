@php
    $title = 'Tin tức - Vie Travel';
@endphp
@extends('layouts.app-guest')

@section('content')
    <!-- PAGE HEADER -->
    <section class="pt-28 pb-10 bg-gradient-to-b from-sky-50 to-white border-b border-gray-200">
        <div class="max-w-screen-xl mx-auto px-4">
            <!-- breadcrumb -->
            <nav class="text-sm text-gray-500 mb-3">
                <ol class="flex items-center gap-2">
                    <li><a href="{{ route('home') }}" class="hover:text-sky-600">Trang chủ</a></li>
                    <li class="text-gray-400">/</li>
                    <li class="text-gray-700 font-medium">Tin tức</li>
                </ol>
            </nav>

            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5">
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900">Tin tức & Bài viết</h1>
                    <p class="text-gray-600 mt-1">
                        Cập nhật những thông tin, mẹo du lịch, và trải nghiệm thú vị từ VieTravel
                    </p>
                </div>

                <!-- quick search -->
                <div class="w-full lg:w-[380px]">
                    <div class="bg-white/90 backdrop-blur border border-gray-200 rounded-2xl shadow-sm p-3">
                        <form class="flex items-center gap-2" method="GET" action="{{ route('news') }}">
                            <input type="text" name="q" value="{{ request('q') }}"
                                placeholder="Tìm tin tức..."
                                class="flex-1 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:ring-4 focus:ring-sky-100 focus:border-sky-500">
                            <button type="submit"
                                class="rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-sky-700 focus:ring-4 focus:ring-sky-200">
                                Tìm
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTENT -->
    <section class="py-10">
        <div class="max-w-screen-xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-4 gap-8">

            <!-- SIDEBAR - FILTER -->
            <aside class="lg:col-span-1 sticky top-28 h-fit">
                <form method="GET" action="{{ route('news') }}" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-lg">Bộ lọc</h3>
                        <a href="{{ route('news') }}" class="text-sm text-gray-500 hover:text-sky-600 hover:underline">Xoá lọc</a>
                    </div>

                    <!-- Categories -->
                    <div class="mb-5">
                        <label class="text-sm font-semibold text-gray-700 block mb-3">Danh mục</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="category" value=""
                                    {{ !request('category') ? 'checked' : '' }}
                                    onchange="this.form.submit()"
                                    class="w-4 h-4 text-sky-600 border-gray-300 focus:ring-sky-500">
                                <span class="text-sm text-gray-700">Tất cả</span>
                            </label>
                            @foreach ($categories as $cat)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="category" value="{{ $cat }}"
                                        {{ request('category') === $cat ? 'checked' : '' }}
                                        onchange="this.form.submit()"
                                        class="w-4 h-4 text-sky-600 border-gray-300 focus:ring-sky-500">
                                    <span class="text-sm text-gray-700">{{ $cat }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Keep search query in filter -->
                    @if (request('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif

                    <!-- Sort Info -->
                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500">📅 Sắp xếp: Mới nhất trước</p>
                    </div>
                </form>
            </aside>

            <!-- MAIN CONTENT -->
            <div class="lg:col-span-3">
                @if ($news->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach ($news as $article)
                            <article
                                class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden hover:shadow-md hover:border-sky-200 transition-all duration-300">
                                <!-- Image -->
                                @if ($article->image)
                                    <div class="h-48 bg-gray-200 overflow-hidden">
                                        <img src="{{ asset('storage/' . $article->image) }}" alt="{{ $article->title }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    </div>
                                @else
                                    <div class="h-48 bg-gradient-to-br from-sky-100 to-sky-50 flex items-center justify-center">
                                        <i class="fas fa-newspaper text-sky-300 text-4xl"></i>
                                    </div>
                                @endif

                                <!-- Content -->
                                <div class="p-5">
                                    <!-- Category Badge -->
                                    <div class="mb-2">
                                        <span
                                            class="inline-flex items-center rounded-full bg-sky-50 text-sky-700 px-3 py-1 text-xs font-medium">
                                            {{ $article->category }}
                                        </span>
                                    </div>

                                    <!-- Title -->
                                    <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 hover:text-sky-600">
                                        {{ $article->title }}
                                    </h3>

                                    <!-- Description -->
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-3">
                                        {{ $article->description }}
                                    </p>

                                    <!-- Meta -->
                                    <div class="flex items-center justify-between text-xs text-gray-500 border-t border-gray-100 pt-3">
                                        <div class="flex items-center gap-2">
                                            @if ($article->author)
                                                <span>{{ $article->author->name }}</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <i class="fas fa-eye"></i>
                                            <span>{{ $article->views }}</span>
                                        </div>
                                    </div>

                                    <!-- Date & Read More -->
                                    <div class="mt-4 flex items-center justify-between">
                                        <time class="text-xs text-gray-500">
                                            {{ $article->published_at->format('d/m/Y') }}
                                        </time>
                                        <a href="{{ route('news.show', $article->slug) }}"
                                            class="text-sky-600 hover:text-sky-700 font-semibold text-sm inline-flex items-center gap-1 group">
                                            Đọc thêm
                                            <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition"></i>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <!-- PAGINATION -->
                    @if ($news->hasPages())
                        <div class="mt-10">
                            <div class="flex justify-center">
                                {{ $news->links() }}
                            </div>
                        </div>
                    @endif
                @else
                    <!-- Empty State -->
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-12 text-center">
                        <i class="fas fa-inbox text-gray-300 text-5xl mb-4 block"></i>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Không có tin tức nào</h3>
                        <p class="text-gray-600 mb-6">
                            Hãy thử thay đổi bộ lọc hoặc quay lại sau
                        </p>
                        <a href="{{ route('news') }}"
                            class="inline-flex items-center gap-2 bg-sky-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-sky-700 transition">
                            <i class="fas fa-redo"></i>
                            Xoá lọc
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-gradient-to-r from-sky-600 to-sky-700 text-white">
        <div class="max-w-screen-xl mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Đón nhận tin tức mới từ VieTravel</h2>
            <p class="text-sky-100 mb-8 max-w-2xl mx-auto">
                Đăng ký nhận bản tin hàng tuần với những ưu đãi độc quyền, mẹo du lịch, và điểm đến mới
            </p>

            <form class="max-w-md mx-auto flex gap-2">
                <input type="email" placeholder="Nhập email của bạn..."
                    class="flex-1 rounded-lg bg-white px-4 py-3 text-gray-900 outline-none focus:ring-4 focus:ring-sky-300">
                <button type="submit"
                    class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-3 rounded-lg font-semibold transition">
                    Đăng ký
                </button>
            </form>
        </div>
    </section>
@endsection
