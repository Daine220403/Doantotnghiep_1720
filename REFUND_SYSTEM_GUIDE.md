# 🔄 Hệ Thống Hoàn Tiền Tour - Hướng Dẫn Sử Dụng

## 📋 Tổng Quan

Hệ thống hoàn tiền toàn diện với quy trình duyệt và xử lý qua VNPay:
1. Khách hủy tour → Tạo yêu cầu hoàn tiền (chờ duyệt)
2. Admin duyệt → Xác nhận hoàn tiền
3. VNPay transfer → Cộng tiền vào ví
4. Khách nhận tiền hoàn lại

## 👥 Cho Khách Hàng

### Hủy Tour
1. Vào Dashboard → Tìm tour muốn hủy
2. Click nút "Hủy"
3. Hệ thống tạo yêu cầu hoàn tiền
4. Trạng thái: **Chờ hoàn tiền** 🟠

### Kiểm Tra Ví Tiền
1. Vào **Ví tiền** (menu chính)
2. Xem số dư hiện tại
3. Xem chi tiết từng giao dịch

**Route:** `/refund-wallet`

### Trạng Thái Hoàn Tiền
| Status | Ý Nghĩa | Biểu Tượng |
|--------|---------|-----------|
| pending | Chờ admin duyệt | 🟡 |
| approved | Đã duyệt, chuẩn bị transfer | 🔵 |
| refunded | Đã hoàn tiền thành công | 🟢 |
| rejected | Bị từ chối | 🔴 |

## 🛠️ Cho Admin

### Quản Lý Yêu Cầu Hoàn Tiền

**Route:** `/admin/refund-requests`

### Các Bước Xử Lý
1. **Xem danh sách** - Tất cả yêu cầu hoàn tiền
2. **Xem chi tiết** - Thông tin khách, tour, số tiền
3. **Duyệt** - Nút "Duyệt yêu cầu" (thêm ghi chú)
4. **Từ chối** - Nút "Từ chối yêu cầu" (nhập lý do)
5. **Xác nhận hoàn tiền** - Xác nhận transfer (test mode)

### Quy Trình Chi Tiết

#### 1️⃣ Danh Sách (`/admin/refund-requests`)
```
- Lọc theo trạng thái: Tất cả / Chờ duyệt / Đã duyệt / Đã hoàn / Từ chối
- Xem thống kê: Tổng tiền, số yêu cầu
- Click vào mã hoàn tiền để xem chi tiết
```

#### 2️⃣ Chi Tiết (`/admin/refund-requests/{id}`)
```
Thông tin hiển thị:
- Khách hàng: Tên, email, điện thoại
- Tour: Tên, điểm đến, ngày khởi hành
- Hoàn tiền: Số tiền, phương thức
- Hành khách: Danh sách chi tiết
- Lịch sử xử lý: Ai duyệt, khi nào

Nút thao tác (nếu chờ duyệt):
- "Duyệt yêu cầu" - Ghi chú (tùy chọn)
- "Từ chối yêu cầu" - Lý do (bắt buộc)
```

#### 3️⃣ Duyệt Yêu Cầu
```
- Nhấn "Duyệt yêu cầu"
- Thêm ghi chú (nếu cần)
- Nhấn để xác nhận
- Hệ thống sẵn sàng xử lý hoàn tiền
```

#### 4️⃣ Xác Nhận Hoàn Tiền
```
- Sau khi duyệt, nhấn "Xác nhận hoàn tiền"
- Test mode: Hoàn tiền thành công ngay
- Real mode: Gọi VNPay API (khi tích hợp)
- Tiền được cộng vào ví khách
- Status: refunded ✓
```

### Thống Kê Dashboard
```
Chờ duyệt:    Số yêu cầu + tổng tiền (🟡)
Đã duyệt:     Tổng tiền đang xử lý (🔵)
Đã hoàn tiền: Số yêu cầu + tổng tiền (🟢)
Từ chối:      Tổng tiền bị từ chối (🔴)
```

## 🗄️ Cơ Sở Dữ Liệu

### Bảng `refund_requests`
- Theo dõi từng yêu cầu hoàn tiền
- Lưu thông tin duyệt/từ chối
- Lưu dữ liệu VNPay response

### Bảng `refund_wallets`
- Số dư ví của mỗi user
- Tổng nhận được / rút được
- Trạng thái ví (active/locked)

### Bảng `refund_wallet_transactions`
- Lịch sử từng giao dịch
- Số dư trước/sau
- Liên kết đến refund request

## 🔌 Quy Trình VNPay (Hiện Tại)

### Test Mode (Hiện Tại)
```php
// admin/RefundRequestController.php
testProcess() - Hoàn tiền giả lập ngay
```

**Sử dụng để kiểm tra:**
1. Duyệt yêu cầu
2. Nhấn "Xác nhận hoàn tiền" (chế độ test)
3. Xem tiền được cộng vào ví

### Real VNPay Integration (Sắp Tới)
```php
// processVNPayRefund() - Cần implement
// Gọi VNPay refund API
// Xử lý response
// Cập nhật wallet
```

## 📊 Quy Trình Dữ Liệu

```
Khách hủy tour
     ↓
RefundService::createRefundRequest()
     ↓
RefundRequest(status: pending)
Booking(status: pending_refund)
     ↓
Admin duyệt
     ↓
RefundService::approveRefundRequest()
RefundRequest(status: approved)
     ↓
Admin xác nhận hoàn tiền
     ↓
RefundService::processRefundSuccess()
     ↓
1. Cộng tiền vào RefundWallet
2. Tạo RefundWalletTransaction
3. Cập nhật RefundRequest(status: refunded)
4. Cập nhật Booking(status: cancelled)
     ↓
Khách nhận tiền trong ví
```

## 🔍 Kiểm Tra & Debug

### Xem Log Refund Requests
```sql
-- Xem tất cả yêu cầu
SELECT * FROM refund_requests ORDER BY created_at DESC;

-- Xem yêu cầu chờ duyệt
SELECT * FROM refund_requests WHERE status = 'pending';

-- Xem theo user
SELECT * FROM refund_requests WHERE user_id = 1;
```

### Xem Ví Khách
```sql
SELECT * FROM refund_wallets WHERE user_id = 1;
SELECT * FROM refund_wallet_transactions WHERE user_id = 1;
```

### Kiểm Tra Booking
```sql
-- Xem booking pending_refund
SELECT * FROM bookings WHERE status = 'pending_refund';
```

## 🚀 Triển Khai

### 1. Migration
```bash
php artisan migrate
```

### 2. Testing
```bash
# Đăng nhập khách hàng
# Hủy tour
# Admin duyệt
# Admin xác nhận (test mode)
# Kiểm tra ví
```

### 3. Tích Hợp VNPay Thực
```php
// app/Http/Controllers/admin/RefundRequestController.php
// Hàm processVNPayRefund() - Sửa thành thực

// Cần:
// - VNPay account credentials
// - Refund API endpoint
// - Xử lý callback từ VNPay
```

## ⚠️ Lưu Ý

- Khách hủy tour chỉ tạo request, không cancel ngay
- Admin phải duyệt trước khi hoàn tiền
- Tiền được cộng vào ví, không transfer ngay về tk
- Khách có thể dùng ví để thanh toán tour khác
- Toàn bộ giao dịch được lưu trữ để audit

## 📞 Support

Nếu gặp lỗi:
1. Kiểm tra log: `storage/logs/`
2. Kiểm tra database consistency
3. Xem status refund request
4. Kiểm tra wallet balance của user

---

**Phiên bản:** 1.0 (Test Mode)  
**Ngày:** 30/04/2026  
**Trạng thái:** Ready for Testing ✓
