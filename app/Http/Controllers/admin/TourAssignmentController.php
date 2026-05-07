<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\tour_departures;
use App\Models\tour_assignments;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TourAssignmentController extends Controller
{
    // Danh sách lịch khởi hành cần phân công HDV
    public function index(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $tourName = $request->input('tour_name');
        $tourCode = $request->input('tour_code');

        $query = tour_departures::with(['tour', 'assignment.guide'])
            ->whereDate('start_date', '>=', $today)
            ->whereNotIn('status', ['cancelled', 'completed']);

        if ($startDate) {
            $query->whereDate('start_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('start_date', '<=', $endDate);
        }

        if ($tourName) {
            $query->whereHas('tour', function ($q) use ($tourName) {
                $q->where('title', 'like', '%' . $tourName . '%');
            });
        }

        if ($tourCode) {
            $query->whereHas('tour', function ($q) use ($tourCode) {
                $q->where('code', 'like', '%' . $tourCode . '%');
            });
        }

        $departures = $query->orderBy('start_date')->get();

        return view('admin.mana_guide.departures_need_assign', compact('departures', 'startDate', 'endDate', 'tourName', 'tourCode'));
    }

    // Chọn HDV phù hợp cho một lịch khởi hành cụ thể
    public function selectGuide(tour_departures $departure)
    {
        // Không cho phân công cho lịch đã hủy / hoàn tất hoặc đã diễn ra
        if (in_array($departure->status, ['cancelled', 'completed']) ||
            $departure->start_date < Carbon::today()->toDateString()) {
            return redirect()->back()->with('error', 'Không thể phân công Hướng dẫn viên cho lịch khởi hành này.');
        }

        $departure->load('tour', 'assignment.guide');

        // Danh sách HDV đang hoạt động, không bị trùng lịch với lịch hiện tại
        $guides = User::where('role', 'tour_guide')
            ->where('status', 'active')
            ->withCount('guideAssignments')
            ->whereDoesntHave('guideAssignments', function ($q) use ($departure) {
                $q->whereHas('departure', function ($q2) use ($departure) {
                    $q2->where(function ($query) use ($departure) {
                        $query->whereBetween('start_date', [$departure->start_date, $departure->end_date])
                              ->orWhereBetween('end_date', [$departure->start_date, $departure->end_date])
                              ->orWhere(function ($sub) use ($departure) {
                                  $sub->where('start_date', '<=', $departure->start_date)
                                      ->where('end_date', '>=', $departure->end_date);
                              });
                    });
                });
            })
            ->orderBy('name')
            ->get();

        return view('admin.mana_guide.select_guide', compact('departure', 'guides'));
    }

    public function assign(tour_departures $departure, Request $request)
    {
        $request->validate([
            'guide_id' => 'nullable|exists:users,id',
        ], [
            'guide_id.exists' => 'Hướng dẫn viên không hợp lệ.',
        ]);

        $guideId = $request->input('guide_id');

        if ($guideId) {
            $guide = User::where('id', $guideId)
                ->where('role', 'tour_guide')
                ->where('status', 'active')
                ->first();

            if (!$guide) {
                return back()->with('error', 'Chỉ có thể phân công tài khoản Hướng dẫn viên đang hoạt động.');
            }

            // Ràng buộc 1: không phân công cho lịch đã hủy / hoàn tất
            if (in_array($departure->status, ['cancelled', 'completed'])) {
                return back()->with('error', 'Không thể phân công Hướng dẫn viên cho lịch khởi hành đã hủy hoặc đã hoàn tất.');
            }

            // Ràng buộc 2: không phân công cho lịch đã qua ngày khởi hành
            if ($departure->start_date < Carbon::today()->toDateString()) {
                return back()->with('error', 'Không thể phân công Hướng dẫn viên cho lịch khởi hành đã diễn ra.');
            }

            // Ràng buộc 3: không để trùng lịch giữa các tour của cùng một HDV
            $hasConflict = tour_assignments::where('guide_id', $guide->id)
                ->where('departure_id', '!=', $departure->id)
                ->whereHas('departure', function ($q) use ($departure) {
                    $q->where(function ($query) use ($departure) {
                        $query->whereBetween('start_date', [$departure->start_date, $departure->end_date])
                              ->orWhereBetween('end_date', [$departure->start_date, $departure->end_date])
                              ->orWhere(function ($sub) use ($departure) {
                                  $sub->where('start_date', '<=', $departure->start_date)
                                      ->where('end_date', '>=', $departure->end_date);
                              });
                    });
                })
                ->exists();

            if ($hasConflict) {
                return back()->with('error', 'Hướng dẫn viên này đã được phân công cho một lịch khởi hành khác trùng thời gian.');
            }

            $assignment = tour_assignments::firstOrNew([
                'departure_id' => $departure->id,
            ]);

            // Nếu đã phân công đúng HDV này rồi thì không cần lưu lại nữa
            if ($assignment->exists && $assignment->guide_id == $guide->id) {
                return back()->with('success', 'Hướng dẫn viên này đã được phân công cho lịch khởi hành này.');
            }

            $assignment->guide_id = $guide->id;
            $assignment->assigned_by = Auth::id();

            if ($assignment->exists === false) {
                $assignment->status = 'assigned';
            }

            $assignment->save();
        } else {
            // Không chọn guide -> huỷ phân công nếu có
            $existing = tour_assignments::where('departure_id', $departure->id)->first();
            if ($existing) {
                $existing->delete();
            }
        }

        return back()->with('success', 'Cập nhật phân công Hướng dẫn viên thành công.');
    }

    // Hủy phân công HDV cho một lịch khởi hành
    public function unassign(tour_departures $departure)
    {
        // Kiểm tra xem lịch có được phân công chưa
        $assignment = tour_assignments::where('departure_id', $departure->id)->first();

        if (!$assignment) {
            return back()->with('error', 'Lịch khởi hành này không có Hướng dẫn viên được phân công.');
        }

        // Kiểm tra xem lịch có hợp lệ để hủy phân công không
        if (in_array($departure->status, ['cancelled', 'completed']) ||
            $departure->start_date < Carbon::today()->toDateString()) {
            return back()->with('error', 'Không thể hủy phân công cho lịch khởi hành này.');
        }

        $guideName = $assignment->guide->name ?? 'N/A';
        $assignment->delete();

        return back()->with('success', 'Hủy phân công Hướng dẫn viên ' . $guideName . ' thành công.');
    }
}
