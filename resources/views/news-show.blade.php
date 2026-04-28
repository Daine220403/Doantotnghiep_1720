@php
    $title = $article->title . ' - Vie Travel';
@endphp
@extends('layouts.app-guest')

@section('content')
    <!-- PAGE HEADER -->
    <section class="pt-28 pb-10 bg-gradient-to-b from-sky-50 to-white border-b border-gray-200">
        <div class="max-w-screen-xl mx-auto px-4">
            <!-- breadcrumb -->
            <nav class="text-sm text-gray-500 mb-4">
                <ol class="flex items-center gap-2">
                    <li><a href="{{ route('home') }}" class="hover:text-sky-600">Trang chủ</a></li>
                    <li class="text-gray-400">/</li>
                    <li><a href="{{ route('news') }}" class="hover:text-sky-600">Tin tức</a></li>
                    <li class="text-gray-400">/</li>
                    <li class="text-gray-700 font-medium line-clamp-1">{{ $article->title }}</li>
                </ol>
            </nav>

            <!-- Title & Meta -->
            <div class="max-w-3xl">
                <!-- Category Badge -->
                <div class="mb-4">
                    <span
                        class="inline-flex items-center rounded-full bg-sky-50 text-sky-700 px-4 py-1.5 text-sm font-medium">
                        {{ $article->category }}
                    </span>
                </div>

                <!-- Title -->
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-gray-900 leading-tight mb-6">
                    {{ $article->title }}
                </h1>

                <!-- Author & Meta -->
                <div class="flex flex-wrap items-center gap-6 text-sm text-gray-600">
                    @if ($article->author)
                        <div class="flex items-center gap-2">
                            <i class="fas fa-user-circle text-sky-600"></i>
                            <span>{{ $article->author->name }}</span>
                        </div>
                    @endif
                    <div class="flex items-center gap-2">
                        <i class="fas fa-calendar text-sky-600"></i>
                        <time>{{ $article->published_at->format('d/m/Y') }}</time>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-eye text-sky-600"></i>
                        <span>{{ $article->views }} lượt xem</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MAIN CONTENT -->
    <section class="py-12 bg-white">
        <div class="max-w-3xl mx-auto px-4">
            <!-- Featured Image -->
            @if ($article->image)
                <div class="mb-12 rounded-2xl overflow-hidden border border-gray-200 shadow-sm">
                    <img src="{{ asset('storage/' . $article->image) }}" alt="{{ $article->title }}"
                        class="w-full h-96 object-cover">
                </div>
            @else
                <div class="mb-12 rounded-2xl overflow-hidden border border-gray-200 shadow-sm bg-gradient-to-br from-sky-100 to-sky-50 h-96 flex items-center justify-center">
                    <i class="fas fa-newspaper text-sky-300 text-6xl"></i>
                </div>
            @endif

            <!-- Article Content -->
            <article class="prose prose-lg max-w-none">
                <!-- Description -->
                <div class="mb-8 text-lg text-gray-700 leading-relaxed italic border-l-4 border-sky-500 pl-6 py-4 bg-sky-50 rounded">
                    {{ $article->description }}
                </div>

                <!-- Content -->
                <div class="text-gray-800 leading-relaxed space-y-6">
                    {!! nl2br(e($article->content)) !!}
                </div>
            </article>

            <!-- Article Footer -->
            <div class="mt-12 pt-8 border-t border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <!-- Share -->
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-semibold text-gray-700">Chia sẻ:</span>
                        <div class="flex gap-2">
                            <a href="#" target="_blank"
                                class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white hover:bg-blue-700 transition"
                                title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" target="_blank"
                                class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-sky-400 text-white hover:bg-sky-500 transition"
                                title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" target="_blank"
                                class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-red-500 text-white hover:bg-red-600 transition"
                                title="Pinterest">
                                <i class="fab fa-pinterest-p"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Back Link -->
                    <a href="{{ route('news') }}"
                        class="inline-flex items-center gap-2 text-sky-600 hover:text-sky-700 font-semibold">
                        <i class="fas fa-arrow-left"></i>
                        Quay lại tin tức
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- RELATED NEWS -->
    @if ($relatedNews->count() > 0)
        <section class="py-14 bg-gray-50">
            <div class="max-w-screen-xl mx-auto px-4">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-8">Tin tức liên quan</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($relatedNews as $related)
                        <article
                            class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-all duration-300">
                            <!-- Image -->
                            @if ($related->image)
                                <div class="h-40 bg-gray-200 overflow-hidden">
                                    <img src="{{ asset('storage/' . $related->image) }}"
                                        alt="{{ $related->title }}"
                                        class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                                </div>
                            @else
                                <div class="h-40 bg-gradient-to-br from-sky-100 to-sky-50 flex items-center justify-center">
                                    <i class="fas fa-newspaper text-sky-300 text-3xl"></i>
                                </div>
                            @endif

                            <!-- Content -->
                            <div class="p-4">
                                <!-- Category -->
                                <span
                                    class="inline-flex items-center rounded-full bg-sky-50 text-sky-700 px-2.5 py-0.5 text-xs font-medium mb-2">
                                    {{ $related->category }}
                                </span>

                                <!-- Title -->
                                <h3 class="text-base font-bold text-gray-900 mb-2 line-clamp-2 hover:text-sky-600">
                                    <a href="{{ route('news.show', $related->slug) }}">
                                        {{ $related->title }}
                                    </a>
                                </h3>

                                <!-- Description -->
                                <p class="text-xs text-gray-600 mb-3 line-clamp-2">
                                    {{ $related->description }}
                                </p>

                                <!-- Meta -->
                                <div class="flex items-center justify-between text-xs text-gray-500 border-t border-gray-100 pt-2">
                                    <time>{{ $related->published_at->format('d/m/Y') }}</time>
                                    <a href="{{ route('news.show', $related->slug) }}"
                                        class="text-sky-600 hover:text-sky-700 font-semibold">
                                        Xem
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- CTA Section -->
    <section class="py-16 bg-gradient-to-r from-sky-600 to-sky-700 text-white">
        <div class="max-w-screen-xl mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Khám phá thêm tour du lịch</h2>
            <p class="text-sky-100 mb-8 max-w-2xl mx-auto">
                Từ những bãi biển xinh đẹp đến những ngọn núi hùng vĩ, VieTravel mang đến những trải nghiệm du lịch tuyệt
                vời
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('tours', ['scope' => 'domestic']) }}"
                    class="inline-flex items-center gap-2 bg-white text-sky-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                    <i class="fas fa-compass"></i>
                    Khám phá tour trong nước
                </a>

                <a href="{{ route('tours', ['scope' => 'international']) }}"
                    class="inline-flex items-center gap-2 bg-amber-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-amber-600 transition">
                    <i class="fas fa-globe"></i>
                    Tour quốc tế
                </a>
            </div>
        </div>
    </section>
@endsection
