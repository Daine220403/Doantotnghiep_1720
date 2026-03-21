<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tours;
use App\Models\bookings;
use App\Models\booking_passengers;
use App\Models\orders;
use App\Models\order_details;
use App\Models\tour_departures;
use App\Models\payments;
use Illuminate\Support\Facades\Auth;

class paymentController extends Controller
{
    protected string $vnp_TmnCode = "KF3WIF8C";
    protected string $vnp_HashSecret = "KCA2KNYAWFXVEUQ4DNH4PQC801ABVGTB";

    public function vnpay_payment(Request $request)
    {
        // Logic checkout: kiểm tra đăng nhập, validate và tạo đơn
        if (!Auth::check()) {
            return redirect()->route('signin')->with('error', 'Vui lòng đăng nhập để đặt tour');
        }

        $data = $request->validate([
            'tour_id' => ['required', 'exists:tours,id'],
            'schedule_id' => ['required', 'exists:tour_departures,id'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
            'infants' => ['nullable', 'integer', 'min:0'],
            'youths' => ['nullable', 'integer', 'min:0'],
            'note' => ['nullable', 'string'],
            'passengers' => ['required', 'array'],
            'passengers.*.full_name' => ['required', 'string', 'max:150'],
            'passengers.*.passenger_type' => ['required', 'in:adult,child,infant,youth'],
            'passengers.*.gender' => ['nullable', 'in:male,female,other'],
            'passengers.*.dob' => ['nullable', 'date'],
            'passengers.*.id_no' => ['nullable', 'string', 'max:50'],
            'passengers.*.special_request' => ['nullable', 'string'],
        ], [], [
            'tour_id' => 'tour',
            'schedule_id' => 'lịch khởi hành',
            'adults' => 'số lượng người lớn',
            'children' => 'số lượng trẻ em',
            'infants' => 'số lượng trẻ nhỏ',
            'youths' => 'số lượng em bé',
            'note' => 'ghi chú',
            'passengers' => 'danh sách hành khách',
            'passengers.*.full_name' => 'tên hành khách',
        ]);

        $contact_name = Auth::user()->name ?? 'Khách hàng';
        $contact_email = Auth::user()->email ?? null;
        $contact_phone = Auth::user()->phone ?? null;

        $tour = Tours::where('id', $data['tour_id'])
            ->where('status', 'published')
            ->firstOrFail();

        $departure = tour_departures::where('id', $data['schedule_id'])
            ->where('tour_id', $tour->id)
            ->firstOrFail();

        if ($departure->start_date < now()->toDateString()) {
            return back()->withErrors(['schedule_id' => 'Lịch khởi hành đã quá hạn'])->withInput();
        }

        if (!in_array($departure->status, ['open', 'sold_out', 'confirmed'])) {
            return back()->withErrors(['schedule_id' => 'Lịch khởi hành không khả dụng'])->withInput();
        }

        $adults = (int) $data['adults'];
        $children = (int) ($data['children'] ?? 0);
        $infants = (int) ($data['infants'] ?? 0);
        $youths = (int) ($data['youths'] ?? 0);
        $totalGuests = $adults + $children + $infants + $youths;

        $passengersInput = $request->input('passengers', []);
        if (count($passengersInput) !== $totalGuests) {
            return back()->withErrors([
                'passengers' => 'Số lượng hành khách không khớp với tổng số khách (người lớn, trẻ em, trẻ nhỏ, em bé)',
            ])->withInput();
        }

        $remaining = $departure->capacity_total - $departure->capacity_booked;
        if ($totalGuests > $remaining) {
            return back()->withErrors([
                'schedule_id' => 'Số lượng khách vượt quá số chỗ còn lại (' . max($remaining, 0) . ' chỗ)',
            ])->withInput();
        }

        $priceAdult = (float) $departure->price_adult;
        $priceChild = (float) $departure->price_child;
        $priceInfant = (float) $departure->price_infant;
        $priceYouth = (float) $departure->price_youth;
        $singleSurcharge = $request->boolean('single_room') ? (float) $departure->single_room_surcharge : 0;

        $subtotal = $adults * $priceAdult
            + $children * $priceChild
            + $infants * $priceInfant
            + $youths * $priceYouth
            + $singleSurcharge;
        $discountTotal = 0;
        $totalAmount = $subtotal - $discountTotal;

        // Mã đơn ngắn gọn hơn: OD + yymmddHis (không thêm random)
        $order = orders::create([
            'order_code' => 'OD' . now()->format('ymdHis'),
            'user_id' => Auth::id(),
            'contact_name' => $contact_name,
            'contact_phone' => $contact_phone,
            'contact_email' => $contact_email,
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'total_amount' => $totalAmount,
            'status' => 'pending',
        ]);

        $booking = bookings::create([
            'order_id' => $order->id,
            'departure_id' => $departure->id,
            'note' => $data['note'] ?? null,
            'status' => 'pending',
        ]);

        foreach ($passengersInput as $p) {
            booking_passengers::create([
                'booking_id' => $booking->id,
                'full_name' => $p['full_name'],
                'gender' => $p['gender'] ?? null,
                'dob' => $p['dob'] ?? null,
                'id_no' => $p['id_no'] ?? null,
                'passenger_type' => $p['passenger_type'] ?? 'adult',
                'special_request' => $p['special_request'] ?? null,
            ]);
        }

        if ($adults > 0) {
            order_details::create([
                'order_id' => $order->id,
                'item_type' => 'tour',
                'item_id' => $tour->id,
                'item_name' => $tour->title . ' - Người lớn',
                'qty' => $adults,
                'unit_price' => $priceAdult,
                'line_total' => $adults * $priceAdult,
                'meta' => json_encode([
                    'schedule_id' => $departure->id,
                    'schedule_date' => $departure->start_date,
                    'type' => 'adult',
                ]),
            ]);
        }

        if ($children > 0) {
            order_details::create([
                'order_id' => $order->id,
                'item_type' => 'tour',
                'item_id' => $tour->id,
                'item_name' => $tour->title . ' - Trẻ em',
                'qty' => $children,
                'unit_price' => $priceChild,
                'line_total' => $children * $priceChild,
                'meta' => json_encode([
                    'schedule_id' => $departure->id,
                    'schedule_date' => $departure->start_date,
                    'type' => 'child',
                ]),
            ]);
        }

        if ($infants > 0) {
            order_details::create([
                'order_id' => $order->id,
                'item_type' => 'tour',
                'item_id' => $tour->id,
                'item_name' => $tour->title . ' - Trẻ nhỏ',
                'qty' => $infants,
                'unit_price' => $priceInfant,
                'line_total' => $infants * $priceInfant,
                'meta' => json_encode([
                    'schedule_id' => $departure->id,
                    'schedule_date' => $departure->start_date,
                    'type' => 'infant',
                ]),
            ]);
        }

        if ($youths > 0) {
            order_details::create([
                'order_id' => $order->id,
                'item_type' => 'tour',
                'item_id' => $tour->id,
                'item_name' => $tour->title . ' - Em bé',
                'qty' => $youths,
                'unit_price' => $priceYouth,
                'line_total' => $youths * $priceYouth,
                'meta' => json_encode([
                    'schedule_id' => $departure->id,
                    'schedule_date' => $departure->start_date,
                    'type' => 'youth',
                ]),
            ]);
        }

        if ($singleSurcharge > 0) {
            order_details::create([
                'order_id' => $order->id,
                'item_type' => 'surcharge',
                'item_id' => $tour->id,
                'item_name' => $tour->title . ' - Phụ thu phòng đơn',
                'qty' => 1,
                'unit_price' => $singleSurcharge,
                'line_total' => $singleSurcharge,
                'meta' => json_encode([
                    'schedule_id' => $departure->id,
                    'schedule_date' => $departure->start_date,
                    'type' => 'single_room_surcharge',
                ]),
            ]);
        }

        $departure->increment('capacity_booked', $totalGuests);

        // Tạo bản ghi thanh toán pending
        $payment = payments::create([
            'order_id' => $order->id,
            'payment_code' => 'PM' . now()->format('YmdHis') . rand(100, 999),
            'method' => 'vnpay',
            'amount' => $order->total_amount,
            'status' => 'pending',
            'transaction_ref' => null,
            'raw_response' => null,
        ]);

        // Sau khi tạo đơn, chuyển sang VNPay để thanh toán
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        // URL callback từ VNPay về Laravel (không dùng file .php riêng)
        $vnp_Returnurl = route('vnpay.return');
        $vnp_TmnCode = $this->vnp_TmnCode; //Mã website tại VNPAY 
        $vnp_HashSecret = $this->vnp_HashSecret; //Chuỗi bí mật

        // Sử dụng mã đơn và tổng tiền thực tế
        $vnp_TxnRef = $order->order_code;
        $vnp_OrderInfo = "Thanh toán tour " . $tour->title;
        $vnp_OrderType = "Tour";
        $vnp_Amount = (int) ($order->total_amount * 100);
        $vnp_Locale = "vn";
        $vnp_BankCode = "NCB";
        $vnp_IpAddr = $request->ip();
        // //Add Params of 2.0.1 Version
        // $vnp_ExpireDate = $_POST['txtexpire'];
        // //Billing
        // $vnp_Bill_Mobile = $_POST['txt_billing_mobile'];
        // $vnp_Bill_Email = $_POST['txt_billing_email'];
        // $fullName = trim($_POST['txt_billing_fullname']);
        // if (isset($fullName) && trim($fullName) != '') {
        //     $name = explode(' ', $fullName);
        //     $vnp_Bill_FirstName = array_shift($name);
        //     $vnp_Bill_LastName = array_pop($name);
        // }
        // $vnp_Bill_Address=$_POST['txt_inv_addr1'];
        // $vnp_Bill_City=$_POST['txt_bill_city'];
        // $vnp_Bill_Country=$_POST['txt_bill_country'];
        // $vnp_Bill_State=$_POST['txt_bill_state'];
        // // Invoice
        // $vnp_Inv_Phone=$_POST['txt_inv_mobile'];
        // $vnp_Inv_Email=$_POST['txt_inv_email'];
        // $vnp_Inv_Customer=$_POST['txt_inv_customer'];
        // $vnp_Inv_Address=$_POST['txt_inv_addr1'];
        // $vnp_Inv_Company=$_POST['txt_inv_company'];
        // $vnp_Inv_Taxcode=$_POST['txt_inv_taxcode'];
        // $vnp_Inv_Type=$_POST['cbo_inv_type'];
        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
            // "vnp_ExpireDate"=>$vnp_ExpireDate,
            // "vnp_Bill_Mobile"=>$vnp_Bill_Mobile,
            // "vnp_Bill_Email"=>$vnp_Bill_Email,
            // "vnp_Bill_FirstName"=>$vnp_Bill_FirstName,
            // "vnp_Bill_LastName"=>$vnp_Bill_LastName,
            // "vnp_Bill_Address"=>$vnp_Bill_Address,
            // "vnp_Bill_City"=>$vnp_Bill_City,
            // "vnp_Bill_Country"=>$vnp_Bill_Country,
            // "vnp_Inv_Phone"=>$vnp_Inv_Phone,
            // "vnp_Inv_Email"=>$vnp_Inv_Email,
            // "vnp_Inv_Customer"=>$vnp_Inv_Customer,
            // "vnp_Inv_Address"=>$vnp_Inv_Address,
            // "vnp_Inv_Company"=>$vnp_Inv_Company,
            // "vnp_Inv_Taxcode"=>$vnp_Inv_Taxcode,
            // "vnp_Inv_Type"=>$vnp_Inv_Type
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
        if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
            $inputData['vnp_Bill_State'] = $vnp_Bill_State;
        }

        //var_dump($inputData);
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        // Chuyển hướng sang trang thanh toán VNPay
        return redirect()->away($vnp_Url);
    }

    public function vnpayReturn(Request $request)
    {
        $vnp_HashSecret = $this->vnp_HashSecret; // Chuỗi bí mật phải trùng với bên gửi đi

        $inputData = $request->all();

        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? null;
        foreach ($inputData as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $vnpData[$key] = $value;
            }
        }

        unset($vnpData['vnp_SecureHash']);
        ksort($vnpData);
        $hashData = '';
        $i = 0;
        foreach ($vnpData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        $orderCode = $vnpData['vnp_TxnRef'] ?? null;
        $responseCode = $vnpData['vnp_ResponseCode'] ?? null;
        $vnpAmount = isset($vnpData['vnp_Amount']) ? ((int) $vnpData['vnp_Amount']) / 100 : null;
        $vnpTransactionNo = $vnpData['vnp_TransactionNo'] ?? ($vnpData['vnp_BankTranNo'] ?? null);

        if ($secureHash === $vnp_SecureHash && $orderCode) {
            $order = orders::where('order_code', $orderCode)->first();

            if ($order) {
                $booking = bookings::where('order_id', $order->id)->first();
                $payment = payments::where('order_id', $order->id)
                    ->where('method', 'vnpay')
                    ->latest()
                    ->first();

                if ($responseCode === '00') {
                    if ($order->status !== 'paid') {
                        $order->status = 'paid';
                        $order->save();
                    }

                    if ($booking && $booking->status !== 'paid') {
                        $booking->status = 'paid';
                        $booking->save();
                    }

                    if ($payment) {
                        $payment->status = 'success';
                        $payment->amount = $vnpAmount ?? $payment->amount;
                        $payment->paid_at = now();
                        $payment->transaction_ref = $vnpTransactionNo;
                        $payment->raw_response = $vnpData;
                        $payment->save();
                    } else {
                        payments::create([
                            'order_id' => $order->id,
                            'payment_code' => 'PM' . now()->format('YmdHis') . rand(100, 999),
                            'method' => 'vnpay',
                            'amount' => $vnpAmount ?? $order->total_amount,
                            'status' => 'success',
                            'paid_at' => now(),
                            'transaction_ref' => $vnpTransactionNo,
                            'raw_response' => $vnpData,
                        ]);
                    }

                    return redirect()->route('home')->with('success', 'Thanh toán VNPay thành công. Cảm ơn bạn đã đặt tour!');
                } else {
                    if ($order->status !== 'failed') {
                        $order->status = 'failed';
                        $order->save();
                    }

                    if ($payment) {
                        $payment->status = 'failed';
                        $payment->amount = $vnpAmount ?? $payment->amount;
                        $payment->transaction_ref = $vnpTransactionNo;
                        $payment->raw_response = $vnpData;
                        $payment->save();
                    }

                    return redirect()->route('home')->with('error', 'Thanh toán VNPay không thành công. Vui lòng thử lại hoặc chọn hình thức khác.');
                }
            }
        }

        return redirect()->route('home')->with('error', 'Không xác thực được giao dịch VNPay.');
    }
    public function payBooking(Request $request, $bookingId)
    {
        if (!Auth::check()) {
            return redirect()->route('signin')->with('error', 'Vui lòng đăng nhập để tiếp tục');
        }

        $user = Auth::user();

        $booking = bookings::with(['order', 'departure.tour'])
            ->where('id', $bookingId)
            ->firstOrFail();

        $order = $booking->order;

        if (!$order || $order->user_id !== $user->id) {
            return redirect()->route('dashboard')->with('error', 'Bạn không có quyền với đơn này');
        }

        if (!in_array($order->status, ['pending', 'failed']) || $booking->status !== 'pending') {
            return redirect()->route('dashboard')
                ->with('error', 'Đơn không hợp lệ để thanh toán');
        }

        $tour = optional(optional($booking->departure)->tour);
        if (!$tour) {
            return redirect()->route('dashboard')->with('error', 'Không tìm thấy thông tin tour cho đơn này');
        }

        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = route('vnpay.return');
        $vnp_TmnCode = $this->vnp_TmnCode;
        $vnp_HashSecret = $this->vnp_HashSecret;

        $vnp_TxnRef = $order->order_code;
        $vnp_OrderInfo = "Thanh toán lại tour " . $tour->title;
        $vnp_OrderType = "Tour";
        $vnp_Amount = (int) ($order->total_amount * 100);
        $vnp_Locale = "vn";
        $vnp_BankCode = "NCB";
        $vnp_IpAddr = $request->ip();

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        // Ghi log thanh toán pending (mỗi lần thanh toán lại tạo 1 bản ghi mới)
        payments::create([
            'order_id' => $order->id,
            'payment_code' => 'PM' . now()->format('YmdHis') . rand(100, 999),
            'method' => 'vnpay',
            'amount' => $order->total_amount,
            'status' => 'pending',
            'transaction_ref' => null,
            'raw_response' => null,
        ]);

        return redirect()->away($vnp_Url);
    }
}
