<?php

namespace App\Http\Controllers;

use App\Models\RefundWallet;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerRefundWalletController extends Controller
{
    /**
     * Xem ví hoàn tiền của khách hàng
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $wallet = $user->getOrCreateRefundWallet();

        $transactions = $wallet->transactions()->paginate(20);

        return view('customer.refund-wallet.index', compact('wallet', 'transactions', 'user'));
    }

    /**
     * Xem chi tiết một giao dịch
     */
    public function show($transactionId)
    {
        $user = Auth::user();
        $wallet = $user->getOrCreateRefundWallet();

        $transaction = $wallet->transactions()
            ->where('id', $transactionId)
            ->firstOrFail();

        return view('customer.refund-wallet.show', compact('transaction', 'wallet', 'user'));
    }
}
