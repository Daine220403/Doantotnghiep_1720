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
}
