<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\PayrollPeriod;
use App\Models\WorkReport;
use App\Models\bookings;
use App\Models\orders;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StaffManagerDashboardController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        if (!$user || ($user->role !== 'staff_manager' && $user->role !== 'admin')) {
            abort(403);
        }

        $today = Carbon::today();

        // Tổng nhân viên
        $totalStaff = User::where('role', 'staff')
            ->where('department_id', $user->department_id)
            ->count();

        // Nhân viên đang làm việc hôm nay
        $staffOnDutyToday = Attendance::where('work_date', $today)
            ->where('status', 'present')
            ->whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            })
            ->count();

        // Nhân viên vắng hôm nay
        $staffAbsentToday = Attendance::where('work_date', $today)
            ->where('status', 'absent')
            ->whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            })
            ->count();

        // Yêu cầu nghỉ phép chờ duyệt
        $pendingLeaves = LeaveRequest::where('status', 'pending')
            ->whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            })
            ->count();

        // Danh sách yêu cầu nghỉ phép chờ duyệt
        $leaveRequests = LeaveRequest::with('user')
            ->where('status', 'pending')
            ->whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Danh sách nhân viên + thông tin chấm công hôm nay
        $staffList = User::where('role', 'staff')
            ->where('department_id', $user->department_id)
            ->with(['attendance' => function ($q) use ($today) {
                $q->where('work_date', $today);
            }])
            ->orderBy('name')
            ->limit(20)
            ->get();

        // Lịch làm việc của nhân viên tuần này
        $workSchedules = WorkSchedule::with('user')
            ->whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            })
            ->whereBetween('work_date', [
                $today->copy()->startOfWeek(),
                $today->copy()->endOfWeek()
            ])
            ->orderBy('work_date')
            ->get();

        // Báo cáo công việc chưa xem
        $unreadReports = WorkReport::where('status', 'pending_review')
            ->whereHas('user', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            })
            ->count();

        // Kỳ lương gần nhất
        $currentPayrollPeriod = PayrollPeriod::where('status', 'active')
            ->latest()
            ->first();

        // Thống kê booking theo nhân viên (top 5)
        $topStaffBookings = bookings::with(['order.user'])
            ->whereHas('order', function ($q) use ($user) {
                $q->whereIn('status', ['paid', 'completed'])
                  ->whereHas('user', function ($subQ) use ($user) {
                      $subQ->where('department_id', $user->department_id);
                  });
            })
            ->whereDate('created_at', '>=', $today->copy()->subDays(30))
            ->get()
            ->groupBy(function ($booking) {
                return optional($booking->order)->user_id;
            })
            ->map(function ($group) {
                $user = optional($group->first())->order?->user;
                return [
                    'user' => $user,
                    'count' => $group->count(),
                    'revenue' => $group->sum(fn($b) => optional($b->order)->total_amount ?? 0)
                ];
            })
            ->filter(function ($item) {
                return $item['user'] !== null;
            })
            ->sortByDesc('count')
            ->take(5)
            ->values();

        return view('admin.staff_manager.dashboard', compact(
            'user',
            'totalStaff',
            'staffOnDutyToday',
            'staffAbsentToday',
            'pendingLeaves',
            'leaveRequests',
            'staffList',
            'workSchedules',
            'unreadReports',
            'currentPayrollPeriod',
            'topStaffBookings'
        ));
    }
}
