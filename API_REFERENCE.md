# 🔧 Refund System - Technical API Reference

## RefundService API

### createRefundRequest()
```php
$refundService = new RefundService();
$refundRequest = $refundService->createRefundRequest($bookingId, $userId);

// Returns: RefundRequest instance
// Throws: Exception if validation fails
```

**What it does:**
1. Validates booking ownership
2. Checks if already cancelled
3. Calculates refund amount from paid transactions
4. Creates RefundRequest with status='pending'
5. Changes booking status to 'pending_refund'

**Database Changes:**
- INSERT into `refund_requests`
- UPDATE `bookings` (status → pending_refund)

---

### approveRefundRequest()
```php
$refundRequest = $refundService->approveRefundRequest($refundRequestId, $adminId, $note);

// Parameters:
// - $refundRequestId: ID của refund request
// - $adminId: ID của admin thực hiện duyệt
// - $note: Optional ghi chú

// Returns: RefundRequest instance (status=approved)
```

**What it does:**
1. Updates status to 'approved'
2. Records admin who approved
3. Saves approval timestamp
4. Stores optional notes

**Database Changes:**
- UPDATE `refund_requests` (status, approved_by, approved_at, approval_note)

---

### rejectRefundRequest()
```php
$refundRequest = $refundService->rejectRefundRequest($refundRequestId, $reason);

// Parameters:
// - $refundRequestId: ID của refund request
// - $reason: Lý do từ chối (bắt buộc)

// Returns: RefundRequest instance (status=rejected)
```

**What it does:**
1. Updates status to 'rejected'
2. Records rejection reason
3. Changes booking back to 'cancelled'
4. Updates order status to 'cancelled'
5. Returns capacity slots to tour

---

### processRefundSuccess()
```php
$refundRequest = $refundService->processRefundSuccess($refundRequestId, $vnpayData);

// Parameters:
// - $refundRequestId: ID của refund request
// - $vnpayData: Array chứa VNPay response (optional)

// Returns: RefundRequest instance (status=refunded)
```

**What it does:**
1. Validates request is in 'approved' status
2. Stores VNPay response data
3. Gets or creates refund wallet for user
4. Adds balance to wallet
5. Creates wallet transaction record
6. Updates booking to 'cancelled'
7. Updates order to 'cancelled'
8. Returns capacity to tour

**Database Changes:**
- UPDATE `refund_requests` (status=refunded, refunded_at)
- INSERT/UPDATE `refund_wallets` (balance, total_received)
- INSERT `refund_wallet_transactions`
- UPDATE `bookings` (status=cancelled)
- UPDATE `orders` (status=cancelled)
- UPDATE `tour_departures` (capacity_booked)

---

### processRefundFailed()
```php
$refundRequest = $refundService->processRefundFailed($refundRequestId, $errorMessage);

// Parameters:
// - $refundRequestId: ID của refund request
// - $errorMessage: Thông tin lỗi

// Returns: RefundRequest instance (status=failed)
```

**What it does:**
1. Updates status to 'failed'
2. Stores error message in vnpay_response

---

### getPendingRefundRequests()
```php
$refunds = $refundService->getPendingRefundRequests($limit = 20);

// Returns: LengthAwarePaginator of RefundRequest
// With eager loaded relationships:
// - booking.departure.tour
// - order
// - user
```

---

### getRefundStatistics()
```php
$stats = $refundService->getRefundStatistics();

// Returns array:
[
    'total' => 5000000,              // Tổng tiền
    'pending' => 1000000,            // Chờ duyệt
    'approved' => 1500000,           // Đã duyệt
    'refunded' => 2000000,           // Đã hoàn
    'rejected' => 500000,            // Từ chối
    'failed' => 0,                   // Thất bại
    'pending_count' => 3,            // Số yêu cầu chờ
    'refunded_count' => 2,           // Số đã hoàn
]
```

---

### getUserWalletTransactions()
```php
$transactions = $refundService->getUserWalletTransactions($userId, $limit = 20);

// Returns: LengthAwarePaginator of RefundWalletTransaction
```

---

## Model Methods

### RefundRequest

**Status Values:**
- `pending` - Chờ duyệt
- `approved` - Đã duyệt
- `refunded` - Đã hoàn tiền
- `rejected` - Bị từ chối
- `failed` - Thất bại

**Methods:**
```php
$refund->isPending()      // bool
$refund->isApproved()     // bool
$refund->isRefunded()     // bool
$refund->isRejected()     // bool
$refund->canApprove()     // bool - Có thể duyệt?
$refund->canReject()      // bool - Có thể từ chối?
$refund->getStatusLabel() // string - Nhãn trạng thái
$refund->getStatusBadgeClass() // string - CSS class
```

**Relationships:**
```php
$refund->booking()        // belongsTo bookings
$refund->order()          // belongsTo orders
$refund->user()           // belongsTo User
$refund->approvedBy()     // belongsTo User (admin)
$refund->walletTransaction() // hasOne RefundWalletTransaction
```

**Scopes:**
```php
RefundRequest::pending()
RefundRequest::approved()
RefundRequest::refunded()
RefundRequest::rejected()
RefundRequest::failed()
```

---

### RefundWallet

**Methods:**
```php
$wallet->addBalance($amount, $type, $description, $relatedId, $relatedType)
// Thêm tiền vào ví, tạo transaction

$wallet->deductBalance($amount, $type, $description, $relatedId, $relatedType)
// Trừ tiền từ ví, tạo transaction

$wallet->lock($reason)    // Khóa ví
$wallet->unlock()         // Mở khóa ví
$wallet->isActive()       // bool
```

**Relationships:**
```php
$wallet->user()           // belongsTo User
$wallet->transactions()   // hasMany RefundWalletTransaction
```

---

### RefundWalletTransaction

**Type Values:**
- `refund` - Hoàn tiền
- `withdrawal` - Rút tiền
- `adjustment` - Điều chỉnh

**Status Values:**
- `pending` - Chờ xử lý
- `completed` - Hoàn thành
- `failed` - Thất bại
- `cancelled` - Hủy

**Scopes:**
```php
RefundWalletTransaction::refunds()      // type='refund'
RefundWalletTransaction::withdrawals()  // type='withdrawal'
RefundWalletTransaction::completed()    // status='completed'
RefundWalletTransaction::failed()       // status='failed'
```

---

## Controller Actions

### Admin: RefundRequestController

#### GET /admin/refund-requests
```php
public function index(Request $request)
// Parameters:
// - status: Filter by status (pending, approved, refunded, rejected, failed, all)
// Returns view with:
// - $refunds: Paginated refund requests
// - $status: Current filter
// - $stats: Statistics array
```

#### GET /admin/refund-requests/{refund}
```php
public function show($refundId)
// Returns detailed view with:
// - $refund: RefundRequest with relationships
// - $payments: Related payments
```

#### POST /admin/refund-requests/{refund}/approve
```php
public function approve(Request $request, $refundId)
// Request parameters:
// - note: Optional ghi chú
// Updates refund request to 'approved'
// Redirects back with success message
```

#### POST /admin/refund-requests/{refund}/reject
```php
public function reject(Request $request, $refundId)
// Request parameters:
// - reason: Lý do từ chối (required)
// Updates refund request to 'rejected'
// Cancels booking
```

#### POST /admin/refund-requests/{refund}/process
```php
public function processRefund($refundId)
// Calls VNPay API to process refund
// Returns JSON response
// Updates wallet if successful
```

#### POST /admin/refund-requests/{refund}/test-process
```php
public function testProcess($refundId)
// Test mode: Giả lập hoàn tiền thành công
// Cộng tiền vào ví
// Redirects back with success message
```

---

### Customer: CustomerRefundWalletController

#### GET /refund-wallet
```php
public function index(Request $request)
// Returns view with:
// - $wallet: User's RefundWallet
// - $transactions: Paginated transactions
// - $user: Current user
```

#### GET /refund-wallet/{transaction}
```php
public function show($transactionId)
// Returns view with:
// - $transaction: RefundWalletTransaction
// - $wallet: User's RefundWallet
// - $user: Current user
```

---

## Database Queries

### Find Pending Refunds
```php
$pending = RefundRequest::where('status', 'pending')
    ->with(['booking.departure.tour', 'order', 'user'])
    ->orderBy('created_at', 'desc')
    ->get();
```

### Get User's Wallet
```php
$wallet = RefundWallet::where('user_id', $userId)->first();
if (!$wallet) {
    $wallet = $user->getOrCreateRefundWallet();
}
```

### Get Wallet Transactions
```php
$transactions = RefundWalletTransaction::where('user_id', $userId)
    ->where('type', 'refund')
    ->orderBy('created_at', 'desc')
    ->get();
```

### Check Refund Status
```php
$refund = RefundRequest::where('refund_code', $code)->first();
echo $refund->getStatusLabel(); // "Chờ duyệt", "Đã hoàn tiền", etc.
```

---

## Error Handling

### Common Exceptions
```php
Exception('Bạn không có quyền với đơn này')
Exception('Đơn này đã bị hủy trước đó')
Exception('Yêu cầu hoàn tiền này không thể được duyệt')
Exception('Không có tiền cần hoàn lại')
Exception('Số dư không đủ để thực hiện giao dịch này')
```

### Try-Catch Example
```php
try {
    $refund = $refundService->createRefundRequest($bookingId, $userId);
    return redirect()->back()->with('success', 'Yêu cầu hủy tour đã được gửi');
} catch (Exception $e) {
    return redirect()->back()->with('error', $e->getMessage());
}
```

---

## Integration with VNPay

### Current Implementation (Test Mode)
```php
// app/Http/Controllers/admin/RefundRequestController.php
private function processVNPayRefund($refund)
{
    // Currently: Giả lập thành công
    // Need to implement: Gọi VNPay refund API
}
```

### Required VNPay Implementation
```php
// 1. Call VNPay refund API
$vnpayResponse = $this->callVNPayRefundAPI([
    'original_transaction_id' => $originalTxnId,
    'refund_amount' => $refund->refund_amount,
]);

// 2. Verify response
if ($vnpayResponse['code'] === '00') {
    // Success
    $this->refundService->processRefundSuccess($refundId, $vnpayResponse);
} else {
    // Failed
    $this->refundService->processRefundFailed($refundId, $vnpayResponse['message']);
}
```

---

## Testing

### Unit Test Example
```php
public function test_create_refund_request()
{
    $user = User::factory()->create();
    $booking = Booking::factory()->create();
    
    $service = new RefundService();
    $refund = $service->createRefundRequest($booking->id, $user->id);
    
    $this->assertEquals('pending', $refund->status);
    $this->assertEquals('pending_refund', $booking->fresh()->status);
}
```

### Integration Test
```php
public function test_complete_refund_flow()
{
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $booking = Booking::factory()->create();
    
    // 1. Create refund
    $refund = (new RefundService())->createRefundRequest($booking->id, $user->id);
    $this->assertEquals('pending', $refund->status);
    
    // 2. Approve
    $refund = (new RefundService())->approveRefundRequest($refund->id, $admin->id);
    $this->assertEquals('approved', $refund->status);
    
    // 3. Process
    $refund = (new RefundService())->processRefundSuccess($refund->id);
    $this->assertEquals('refunded', $refund->status);
    
    // 4. Verify wallet
    $wallet = $user->refundWallet;
    $this->assertGreaterThan(0, $wallet->balance);
}
```

---

**Version:** 1.0  
**Last Updated:** 30/04/2026
