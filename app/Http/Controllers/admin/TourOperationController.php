<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\tour_departures;
use App\Models\departure_services;
use App\Models\partner_services;
use Illuminate\Http\Request;

class TourOperationController extends Controller
{
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

        $services = partner_services::with('partner')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.mana_partner.departure_services', compact('departure', 'services'));
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

        $service->delete();

        return redirect()->route('admin.departures.services.index', $departure->id)
            ->with('success', 'Xóa dịch vụ đối tác thành công.');
    }
}
