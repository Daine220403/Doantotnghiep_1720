<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\WorkSchedule;
use App\Models\LeaveRequest;
use App\Models\Attendance;
use App\Models\PayrollPeriod;
use App\Models\PayrollItem;
use App\Models\WorkReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffManagementController extends Controller
{
    // Phân công & xem lịch làm việc của nhân viên
    public function schedulesIndex(Request $request)
    {
        $query = WorkSchedule::with(['staff', 'manager'])->orderByDesc('work_date');

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('work_date', $request->date);
        }

        $schedules = $query->paginate(15);

        $staffs = User::whereIn('role', ['staff'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.hr.schedules_index', compact('schedules', 'staffs'));
    }

    // Danh sách & duyệt đơn nghỉ phép
    public function leavesIndex(Request $request)
    {
        $query = LeaveRequest::with(['staff', 'manager'])->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaves = $query->paginate(15);

        return view('admin.hr.leaves_index', compact('leaves'));
    }

    public function approveLeave(LeaveRequest $leave)
    {
        $leave->update([
            'status' => 'approved',
            'manager_id' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Đã duyệt đơn nghỉ phép.');
    }

    public function rejectLeave(LeaveRequest $leave, Request $request)
    {
        $leave->update([
            'status' => 'rejected',
            'manager_id' => Auth::id(),
            'approved_at' => now(),
            'approved_note' => $request->input('approved_note'),
        ]);

        return back()->with('success', 'Đã từ chối đơn nghỉ phép.');
    }

    // Chấm công nhân viên (xem danh sách chấm công)
    public function attendancesIndex(Request $request)
    {
        $query = Attendance::with(['staff', 'schedule'])->orderByDesc('work_date');

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('work_date', $request->date);
        }

        $attendances = $query->paginate(20);

        $staffs = User::where('role', 'staff')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.hr.attendances_index', compact('attendances', 'staffs'));
    }

    // Kỳ lương và bảng lương
    public function payrollsIndex()
    {
        $periods = PayrollPeriod::with(['items.staff'])
            ->orderByDesc('start_date')
            ->paginate(10);

        return view('admin.hr.payrolls_index', compact('periods'));
    }

    // Báo cáo công việc nhân viên
    public function reportsIndex(Request $request)
    {
        $query = WorkReport::with(['staff', 'manager'])->orderByDesc('report_date');

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        $reports = $query->paginate(20);

        $staffs = User::where('role', 'staff')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.hr.reports_index', compact('reports', 'staffs'));
    }

    // ===== Khu vực nhân viên tự xem / thao tác =====

    // Lịch làm việc của tôi
    public function mySchedules()
    {
        $userId = Auth::id();

        $schedules = WorkSchedule::where('staff_id', $userId)
            ->orderByDesc('work_date')
            ->paginate(15);

        return view('admin.staff_hr.my_schedules', compact('schedules'));
    }

    // Đơn nghỉ phép của tôi
    public function myLeavesIndex()
    {
        $userId = Auth::id();

        $leaves = LeaveRequest::where('staff_id', $userId)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.staff_hr.my_leaves', compact('leaves'));
    }

    public function myLeavesStore(Request $request)
    {
        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'leave_type' => ['required', 'in:annual,sick,unpaid,other'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $data['staff_id'] = Auth::id();
        $data['status'] = 'pending';

        LeaveRequest::create($data);

        return redirect()->route('admin.staff-hr.leaves.index')
            ->with('success', 'Gửi đơn nghỉ phép thành công, vui lòng chờ quản lý duyệt.');
    }

    // Bảng chấm công của tôi
    public function myAttendances()
    {
        $userId = Auth::id();

        $attendances = Attendance::where('staff_id', $userId)
            ->orderByDesc('work_date')
            ->paginate(20);

        return view('admin.staff_hr.my_attendances', compact('attendances'));
    }

    // Báo cáo công việc của tôi
    public function myReportsIndex()
    {
        $userId = Auth::id();

        $reports = WorkReport::where('staff_id', $userId)
            ->orderByDesc('report_date')
            ->paginate(15);

        return view('admin.staff_hr.my_reports', compact('reports'));
    }

    public function myReportsStore(Request $request)
    {
        $data = $request->validate([
            'report_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'total_tasks' => ['nullable', 'integer', 'min:0'],
            'total_hours' => ['nullable', 'numeric', 'min:0'],
        ]);

        $data['staff_id'] = Auth::id();
        $data['status'] = 'submitted';

        WorkReport::create($data);

        return redirect()->route('admin.staff-hr.reports.index')
            ->with('success', 'Đã gửi báo cáo công việc.');
    }
}
