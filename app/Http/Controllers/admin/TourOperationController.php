<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\tour_departures;
use App\Models\departure_services;
use App\Models\partner_services;
use App\Models\partners;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TourOperationController extends Controller
{
    // Danh sách tour đang chạy (các lịch khởi hành đã chốt / đang chạy / hoàn thành)
    public function runningToursIndex()
    {
        // Tự động cập nhật các lịch khởi hành đã đến ngày bắt đầu từ "confirmed" sang "running"
        tour_departures::where('status', 'confirmed')
            ->whereDate('start_date', '<=', now()->toDateString())
            ->update(['status' => 'running']);

        $departures = tour_departures::with(['tour', 'assignment.guide'])
            ->whereIn('status', ['confirmed', 'running', 'completed'])
            ->orderBy('start_date', 'asc')
            ->get();

        return view('admin.mana_running.index', compact('departures'));
    }

    // Chi tiết 1 tour đang chạy theo lịch khởi hành
    public function showRunningTour($departureId)
    {
        $departure = tour_departures::with([
            'tour',
            'assignment.guide',
            'bookings.order',
            'bookings.passengers',
        ])->findOrFail($departureId);

        return view('admin.mana_running.show', compact('departure'));
    }

    // Danh sách tour điều phối (các lịch khởi hành đã chốt / hoàn thành)
    public function coordinatedToursIndex()
    {
        $departures = tour_departures::with(['tour', 'services'])
            ->whereIn('status', ['confirmed', 'completed'])
            ->orderBy('start_date', 'asc')
            ->get();

        return view('admin.mana_partner.coordinatedTours', compact('departures'));
    }

    // Dịch vụ đối tác theo 1 lịch khởi hành (điều phối dịch vụ)
    public function servicesIndex($departureId)
    {
        $departure = tour_departures::with(['tour', 'services.partnerService.partner'])
            ->findOrFail($departureId);

        $partners = partners::where('status', 'active')
            ->orderBy('name')
            ->get();

        $services = partner_services::with('partner')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.mana_partner.departure_services', compact('departure', 'services', 'partners'));
    }

    public function servicesStore(Request $request, $departureId)
    {
        $departure = tour_departures::findOrFail($departureId);

        // Chỉ cho phép điều phối dịch vụ khi lịch khởi hành đã được chốt đoàn
        if ($departure->status !== 'confirmed') {
            return redirect()->back()->with('error', 'Chỉ được điều phối dịch vụ khi lịch khởi hành đã được chốt đoàn.');
        }

        $data = $request->validate([
            'partner_service_id' => 'required|exists:partner_services,id',
            'service_date' => 'nullable|date',
            'service_start_date' => 'nullable|date',
            'service_end_date' => 'nullable|date|after_or_equal:service_start_date',
            'qty' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $data['departure_id'] = $departure->id;
        // Luôn đặt trạng thái là pending khi tạo mới dịch vụ cho lịch khởi hành
        $data['status'] = 'pending';
        $data['total_price'] = $data['qty'] * $data['unit_price'];

        departure_services::create($data);

        return redirect()->route('admin.departures.services.index', $departure->id)
            ->with('success', 'Thêm dịch vụ đối tác cho lịch khởi hành thành công.');
    }

    public function servicesDestroy($departureId, $id)
    {
        $departure = tour_departures::findOrFail($departureId);

        // Chỉ cho phép xóa/chỉnh sửa dịch vụ khi lịch khởi hành đã được chốt đoàn
        if ($departure->status !== 'confirmed') {
            return redirect()->back()->with('error', 'Chỉ được chỉnh sửa dịch vụ khi lịch khởi hành đã được chốt đoàn.');
        }

        $service = departure_services::where('departure_id', $departure->id)->findOrFail($id);
        // Không cho xóa dịch vụ đã được duyệt/hoàn tất khi tour đã chạy
        // hoặc nằm trong khoảng 7 ngày tính tới ngày khởi hành
        if (in_array($service->status, ['confirmed', 'completed'])) {
            $diffDays = now()->diffInDays(Carbon::parse($departure->start_date), false);
            if ($diffDays <= 7) {
                return redirect()->back()->with('error', 'Không được xóa dịch vụ đã được duyệt khi tour đã chạy hoặc trong vòng 7 ngày trước ngày khởi hành.');
            }
        }
        $service->delete();

        return redirect()->route('admin.departures.services.index', $departure->id)
            ->with('success', 'Xóa dịch vụ đối tác thành công.');
    }

    // Yêu cầu hủy dịch vụ (không xóa record, chỉ đổi trạng thái)
    public function servicesRequestCancel($departureId, $id)
    {
        $departure = tour_departures::findOrFail($departureId);

        if ($departure->status !== 'confirmed') {
            return redirect()->back()->with('error', 'Chỉ được yêu cầu hủy dịch vụ khi lịch khởi hành đã được chốt đoàn.');
        }

        $service = departure_services::where('departure_id', $departure->id)->findOrFail($id);

        if ($service->status !== 'confirmed') {
            return redirect()->back()->with('error', 'Chỉ có thể yêu cầu hủy đối với dịch vụ đã được đối tác xác nhận.');
        }

        if ($service->cancel_requested) {
            return redirect()->back()->with('error', 'Dịch vụ này đã được yêu cầu hủy trước đó.');
        }

        $service->cancel_requested = true;
        $service->cancel_requested_at = now();
        $service->save();

        return redirect()->route('admin.departures.services.index', $departure->id)
            ->with('success', 'Đã gửi yêu cầu hủy dịch vụ tới đối tác.');
    }
}
