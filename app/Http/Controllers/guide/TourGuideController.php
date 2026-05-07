<?php

namespace App\Http\Controllers\guide;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\tour_departures;
use App\Models\bookings;
use App\Models\booking_passengers;
use App\Models\DepartureReport;
use Illuminate\Http\Request;

class TourGuideController extends Controller
{
    // Danh sách tour/lịch khởi hành được phân công cho hướng dẫn viên
    public function index(Request $request)
    {
        $user = Auth::user();
        // dd($user);
        // chỉ cho phép người dùng có role 'tour_guide' và 'admin' và đang hoạt động truy cập
        if (!$user || ($user->role !== 'tour_guide' && $user->role !== 'admin') || $user->status !== 'active') {
            abort(403);
        }

        // Xây dựng query với các bộ lọc
        $query = tour_departures::with(['tour', 'assignment'])
            ->whereHas('assignment', function ($q) use ($user) {
                $q->where('guide_id', $user->id);
            });

        // Bộ lọc trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Bộ lọc từ ngày
        if ($request->filled('from_date')) {
            $query->whereDate('start_date', '>=', $request->get('from_date'));
        }

        // Bộ lọc đến ngày
        if ($request->filled('to_date')) {
            $query->whereDate('start_date', '<=', $request->get('to_date'));
        }

        // Bộ lọc tìm kiếm theo mã hoặc tên tour
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->get('search') . '%';
            $query->whereHas('tour', function ($q) use ($searchTerm) {
                $q->where('code', 'like', $searchTerm)
                  ->orWhere('title', 'like', $searchTerm);
            });
        }

        $departures = $query->orderBy('start_date', 'desc')->get();
        
        // Lấy danh sách các trạng thái từ dữ liệu hiện có
        $statuses = tour_departures::distinct()
            ->whereHas('assignment', function ($q) use ($user) {
                $q->where('guide_id', $user->id);
            })
            ->pluck('status')
            ->sort()
            ->values();

        return view('admin.tour_guide.assignments_index', compact('user', 'departures', 'statuses'));
    }

    // Chi tiết 1 lịch khởi hành + danh sách khách
    public function showDeparture($departureId)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'tour_guide') {
            abort(403);
        }

        $departure = tour_departures::with([
            'tour',
            'assignment.guide',
            'bookings.order',
            'bookings.passengers',
        ])->findOrFail($departureId);

        if (!$departure->assignment || $departure->assignment->guide_id !== $user->id) {
            abort(403);
        }

        return view('admin.tour_guide.departure_show', compact('user', 'departure'));
    }

    // Báo cáo thống kê cho 1 lịch khởi hành
    public function report($departureId)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'tour_guide') {
            abort(403);
        }

        $departure = tour_departures::with([
            'tour.itineraries',
            'bookings.order',
            'bookings.passengers',
            'report',
            'services.partnerService',
        ])->findOrFail($departureId);

        if (!$departure->assignment || $departure->assignment->guide_id !== $user->id) {
            abort(403);
        }

        $bookings = $departure->bookings;

        $totalBookings = $bookings->count();
        $totalPassengers = 0;
        $byType = [
            'adult' => 0,
            'child' => 0,
            'infant' => 0,
            'youth' => 0,
        ];
        $singleRoomCount = 0;
        $singleRoomSurchargeTotal = 0;
        $totalRevenue = 0;

        foreach ($bookings as $booking) {
            foreach ($booking->passengers as $p) {
                $totalPassengers++;
                $type = $p->passenger_type ?? 'adult';
                if (isset($byType[$type])) {
                    $byType[$type]++;
                }

                if ($p->single_room) {
                    $singleRoomCount++;
                    $singleRoomSurchargeTotal += (float) ($p->single_room_surcharge ?? 0);
                }
            }

            if ($booking->order) {
                $totalRevenue += (float) ($booking->order->total_amount ?? 0);
            }
        }

        $serviceCostTotal = $departure->services->sum(function ($service) {
            return (float) ($service->total_price ?? 0);
        });

        // Lấy hoặc khởi tạo báo cáo cho lịch khởi hành này
        $report = $departure->report;
        if (!$report) {
            $report = new DepartureReport([
                'departure_id' => $departure->id,
                'guide_id' => $user->id,
            ]);
        }

        $extraCostTotal = (float) ($report->extra_cost_total ?? 0);
        $totalCost = $serviceCostTotal + $extraCostTotal;
        $grossProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        return view('admin.tour_guide.departure_report', [
            'user' => $user,
            'departure' => $departure,
            'totalBookings' => $totalBookings,
            'totalPassengers' => $totalPassengers,
            'byType' => $byType,
            'singleRoomCount' => $singleRoomCount,
            'singleRoomSurchargeTotal' => $singleRoomSurchargeTotal,
            'report' => $report,
            'totalRevenue' => $totalRevenue,
            'serviceCostTotal' => $serviceCostTotal,
            'extraCostTotal' => $extraCostTotal,
            'totalCost' => $totalCost,
            'grossProfit' => $grossProfit,
            'profitMargin' => $profitMargin,
        ]);
    }

    // Lưu / cập nhật báo cáo cho 1 lịch khởi hành
    public function storeReport(Request $request, $departureId)
    {
        dd($request->all());
        $user = Auth::user();

        if (!$user || $user->role !== 'tour_guide') {
            abort(403);
        }

        $request->validate([
            'summary' => 'nullable|string',
            'general_evaluation' => 'nullable|string',
            'itinerary_notes' => 'nullable|string',
            'customer_feedback' => 'nullable|string',
            'guide_suggestion' => 'nullable|string',
            'incidents_rows' => 'nullable|array',
            'incidents_rows.*.description' => 'nullable|string',
            'incidents_rows.*.cost' => 'nullable|numeric|min:0',
        ]);

        $departure = tour_departures::with('assignment')->findOrFail($departureId);

        if (!$departure->assignment || $departure->assignment->guide_id !== $user->id) {
            abort(403);
        }

        $report = DepartureReport::firstOrNew([
            'departure_id' => $departure->id,
        ]);

        $report->guide_id = $user->id;
        $report->summary = $request->input('summary');
        $report->general_evaluation = $request->input('general_evaluation');
        $report->itinerary_notes = $request->input('itinerary_notes');
        $report->customer_feedback = $request->input('customer_feedback');
        $report->guide_suggestion = $request->input('guide_suggestion');

        $incidentRows = $request->input('incidents_rows', []);
        $normalizedRows = [];
        $extraCostTotal = 0;

        foreach ($incidentRows as $row) {
            $description = trim($row['description'] ?? '');
            $cost = isset($row['cost']) ? (float) $row['cost'] : 0;

            if ($description === '' && $cost <= 0) {
                continue;
            }

            $normalizedRows[] = [
                'description' => $description,
                'cost' => $cost,
            ];

            $extraCostTotal += $cost;
        }

        $report->incidents = $normalizedRows ? json_encode($normalizedRows) : null;
        $report->extra_cost_total = $extraCostTotal;
        $report->status = 'submitted';

        $report->save();

        return redirect()
            ->route('guide.departures.report', $departure->id)
            ->with('success', 'Báo cáo đã được lưu thành công.');
    }
}
