@php
    $title = 'Ví tiền hoàn lại';
@endphp

@extends('layouts.app-guest')

@section('content')
    <section class="pt-32 pb-16 bg-gray-50 min-h-screen">
        <div class="max-w-screen-xl mx-auto px-4">
            {{-- Header --}}
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $title }}</h1>
                <p class="text-gray-600">
                    Quản lý số dư ví hoàn lại từ các đơn hủy tour
                </p>
            </div>

            {{-- Balance Card --}}
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-2xl shadow-lg p-8 mb-8 text-white">
                <p class="text-green-100 mb-2">Số dư hiện tại</p>
                <p class="text-5xl font-bold mb-6">{{ number_format($wallet->balance, 0, ',', '.') }} đ</p>
                <div class="grid grid-cols-3 gap-4 pt-6 border-t border-green-400">
                    <div>
                        <p class="text-sm text-green-100 mb-1">Tổng nhận được</p>
                        <p class="text-xl font-semibold">{{ number_format($wallet->total_received, 0, ',', '.') }} đ</p>
                    </div>
                    <div>
                        <p class="text-sm text-green-100 mb-1">Tổng đã rút</p>
                        <p class="text-xl font-semibold">{{ number_format($wallet->total_withdrawn, 0, ',', '.') }} đ</p>
                    </div>
                    <div>
                        <p class="text-sm text-green-100 mb-1">Trạng thái</p>
                        <p class="text-xl font-semibold">{{ $wallet->status === 'active' ? '✓ Hoạt động' : 'Bị khóa' }}</p>
                    </div>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Lịch sử giao dịch</h2>

                @if ($transactions->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 border-b border-gray-200">
                                    <th class="py-3 px-4">Mã giao dịch</th>
                                    <th class="py-3 px-4">Loại</th>
                                    <th class="py-3 px-4">Số tiền</th>
                                    <th class="py-3 px-4">Số dư sau</th>
                                    <th class="py-3 px-4">Mô tả</th>
                                    <th class="py-3 px-4">Ngày</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($transactions as $transaction)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-3 px-4">
                                            <a href="{{ route('customer.refund-wallet.show', $transaction->id) }}"
                                                class="text-blue-600 hover:underline font-mono text-sm">
                                                {{ $transaction->transaction_code }}
                                            </a>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if ($transaction->type === 'refund')
                                                <span class="inline-block px-2 py-1 bg-green-100 text-green-800 text-xs rounded-lg font-semibold">
                                                    Hoàn tiền
                                                </span>
                                            @elseif ($transaction->type === 'withdrawal')
                                                <span class="inline-block px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded-lg font-semibold">
                                                    Rút tiền
                                                </span>
                                            @elseif ($transaction->type === 'adjustment')
                                                <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-lg font-semibold">
                                                    Điều chỉnh
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 font-semibold">
                                            <span class="{{ $transaction->type === 'refund' || $transaction->type === 'adjustment' ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $transaction->type === 'refund' || $transaction->type === 'adjustment' ? '+' : '-' }}
                                                {{ number_format($transaction->amount, 0, ',', '.') }} đ
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            {{ number_format($transaction->balance_after, 0, ',', '.') }} đ
                                        </td>
                                        <td class="py-3 px-4 text-sm text-gray-600">
                                            {{ $transaction->description }}
                                        </td>
                                        <td class="py-3 px-4 text-sm text-gray-600">
                                            {{ $transaction->created_at->format('d/m/Y H:i') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        {{ $transactions->links() }}
                    </div>
                @else
                    <div class="border border-dashed border-gray-300 rounded-lg p-8 text-center">
                        <p class="text-gray-600 mb-2">Chưa có giao dịch nào</p>
                        <p class="text-sm text-gray-500">
                            Các đơn hủy tour của bạn sẽ được hiển thị ở đây
                        </p>
                    </div>
                @endif
            </div>

            {{-- Info Box --}}
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 mt-8">
                <h3 class="font-semibold text-blue-900 mb-3">ℹ️ Hướng dẫn sử dụng ví hoàn tiền</h3>
                <ul class="space-y-2 text-sm text-blue-800">
                    <li>• Khi bạn hủy tour, số tiền sẽ được hoàn lại vào ví này</li>
                    <li>• Số dư ví có thể dùng để thanh toán cho các tour tiếp theo</li>
                    <li>• Bạn cũng có thể rút tiền từ ví hoàn tiền về tài khoản ngân hàng</li>
                    <li>• Mỗi giao dịch sẽ được ghi nhận và hiển thị trong lịch sử</li>
                </ul>
            </div>

            {{-- Back Button --}}
            <div class="mt-8">
                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition">
                    ← Quay lại Dashboard
                </a>
            </div>
        </div>
    </section>
@endsection
