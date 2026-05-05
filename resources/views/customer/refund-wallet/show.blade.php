@php
    $title = 'Chi tiết giao dịch - ' . $transaction->transaction_code;
@endphp

@extends('layouts.app-guest')

@section('content')
    <section class="pt-32 pb-16 bg-gray-50 min-h-screen">
        <div class="max-w-2xl mx-auto px-4">
            {{-- Header --}}
            <div class="mb-8">
                <a href="{{ route('customer.refund-wallet.index') }}" class="text-blue-600 hover:underline text-sm font-semibold mb-3 inline-block">
                    ← Quay lại ví tiền
                </a>
                <h1 class="text-3xl font-bold text-gray-900">{{ $title }}</h1>
            </div>

            {{-- Transaction Details --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-6">
                {{-- Transaction Header --}}
                <div class="border-b border-gray-200 pb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Loại giao dịch</p>
                            <div>
                                @if ($transaction->type === 'refund')
                                    <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-sm font-semibold rounded-lg">
                                        ✓ Hoàn tiền
                                    </span>
                                @elseif ($transaction->type === 'withdrawal')
                                    <span class="inline-block px-3 py-1 bg-orange-100 text-orange-800 text-sm font-semibold rounded-lg">
                                        → Rút tiền
                                    </span>
                                @elseif ($transaction->type === 'adjustment')
                                    <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-lg">
                                        ⚙️ Điều chỉnh
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600 mb-1">Số tiền</p>
                            <p class="text-4xl font-bold {{ $transaction->type === 'refund' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->type === 'refund' ? '+' : '-' }}
                                {{ number_format($transaction->amount, 0, ',', '.') }} đ
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Transaction Info --}}
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-600 mb-2">Mã giao dịch</p>
                        <p class="text-base font-mono font-semibold text-gray-900">{{ $transaction->transaction_code }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-2">Trạng thái</p>
                        <span class="inline-block px-2 py-1 rounded-lg text-sm font-semibold {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $transaction->status === 'completed' ? 'Hoàn thành' : 'Đang xử lý' }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-2">Số dư trước</p>
                        <p class="text-base font-semibold text-gray-900">{{ number_format($transaction->balance_before, 0, ',', '.') }} đ</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-2">Số dư sau</p>
                        <p class="text-base font-semibold text-gray-900">{{ number_format($transaction->balance_after, 0, ',', '.') }} đ</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-sm text-gray-600 mb-2">Ngày tạo</p>
                        <p class="text-base font-semibold text-gray-900">{{ $transaction->created_at->format('d/m/Y H:i:s') }}</p>
                    </div>
                </div>

                {{-- Description --}}
                @if ($transaction->description)
                    <div class="border-t border-gray-200 pt-6">
                        <p class="text-sm text-gray-600 mb-2">Mô tả</p>
                        <p class="text-base text-gray-900 bg-gray-50 p-4 rounded-lg">{{ $transaction->description }}</p>
                    </div>
                @endif

                {{-- Related Refund Request --}}
                @if ($transaction->refund_request)
                    <div class="border-t border-gray-200 pt-6">
                        <p class="text-sm text-gray-600 mb-3">Liên kết đơn hủy tour</p>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm mb-2">
                                <span class="font-semibold">Mã hoàn tiền:</span>
                                {{ $transaction->refund_request->refund_code }}
                            </p>
                            <p class="text-sm mb-2">
                                <span class="font-semibold">Tour:</span>
                                {{ $transaction->refund_request->booking->departure->tour->title }}
                            </p>
                            <p class="text-sm">
                                <span class="font-semibold">Trạng thái đơn:</span>
                                <span class="inline-block px-2 py-1 rounded-lg text-xs font-semibold {{ $transaction->refund_request->getStatusBadgeClass() }}">
                                    {{ $transaction->refund_request->getStatusLabel() }}
                                </span>
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Current Balance --}}
                <div class="border-t border-gray-200 pt-6 bg-blue-50 p-4 rounded-lg">
                    <p class="text-sm text-blue-600 font-semibold mb-1">Số dư ví hiện tại</p>
                    <p class="text-2xl font-bold text-blue-900">{{ number_format($wallet->balance, 0, ',', '.') }} đ</p>
                </div>
            </div>

            {{-- Back Button --}}
            <div class="mt-8">
                <a href="{{ route('customer.refund-wallet.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition">
                    ← Quay lại ví tiền
                </a>
            </div>
        </div>
    </section>
@endsection
