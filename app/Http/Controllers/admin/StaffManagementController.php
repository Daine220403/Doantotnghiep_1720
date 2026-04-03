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
use Carbon\Carbon;

class StaffManagementController extends Controller
{
    // Phân công & xem lịch làm việc của nhân viên
    public function schedulesIndex(Request $request)
    {
        $today = Carbon::today();

        $month = (int) $request->input('month', $today->month);
        $year = (int) $request->input('year', $today->year);
        $selectedStaffId = $request->input('staff_id');

        $current = Carbon::create($year, $month, 1); // ngày 1 của tháng được chọn
        $startOfMonth = $current->copy()->startOfMonth(); // dùng copy() để tránh thay đổi $current
        $endOfMonth = $current->copy()->endOfMonth();

        $scheduleQuery = WorkSchedule::with(['staff', 'manager'])
            ->whereBetween('work_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->orderBy('work_date');

        if (!empty($selectedStaffId)) {
            $scheduleQuery->where('staff_id', $selectedStaffId);
        }

        $schedules = $scheduleQuery->get();

        $schedulesByDate = $schedules->groupBy(function ($item) {
            return $item->work_date->toDateString();
        });

        // Lấy các đơn nghỉ phép đã duyệt, giao với tháng đang xem
        $approvedLeaves = LeaveRequest::with('staff')
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $endOfMonth->toDateString())
            ->whereDate('end_date', '>=', $startOfMonth->toDateString())
            ->get();

        $leaveNamesByDate = [];
        $unavailableStaffByDate = [];

        foreach ($approvedLeaves as $leave) {
            $cursor = $leave->start_date->copy()->max($startOfMonth);
            $end = $leave->end_date->copy()->min($endOfMonth);

            while ($cursor->lte($end)) {
                $dateKey = $cursor->toDateString();

                $leaveNamesByDate[$dateKey] = $leaveNamesByDate[$dateKey] ?? [];
                $unavailableStaffByDate[$dateKey] = $unavailableStaffByDate[$dateKey] ?? [];

                $leaveNamesByDate[$dateKey][] = optional($leave->staff)->name;
                $unavailableStaffByDate[$dateKey][] = $leave->staff_id;

                $cursor->addDay();
            }
        }

        // Ràng buộc: một nhân viên không được phân công quá 5 ngày làm việc (thứ 2 - thứ 6) trong cùng 1 tuần
        $weekWorkingDays = [];

        foreach ($schedules as $schedule) {
            if (!$schedule->work_date) {
                continue;
            }

            // Chỉ tính ngày trong tuần (thứ 2 - thứ 6)
            if ($schedule->work_date->isWeekend()) {
                continue;
            }

            $weekStart = $schedule->work_date->copy()->startOfWeek(Carbon::MONDAY);
            $weekKey = $schedule->staff_id . '|' . $weekStart->toDateString();

            if (!isset($weekWorkingDays[$weekKey])) {
                $weekWorkingDays[$weekKey] = 0;
            }

            $weekWorkingDays[$weekKey]++;
        }

        foreach ($weekWorkingDays as $weekKey => $count) {
            if ($count < 5) {
                continue;
            }

            [$staffId, $weekStartString] = explode('|', $weekKey);
            $weekStart = Carbon::parse($weekStartString);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

            $cursor = $weekStart->copy()->max($startOfMonth);
            $end = $weekEnd->copy()->min($endOfMonth);

            while ($cursor->lte($end)) {
                $dateKey = $cursor->toDateString();

                $unavailableStaffByDate[$dateKey] = $unavailableStaffByDate[$dateKey] ?? [];
                if (!in_array((int) $staffId, $unavailableStaffByDate[$dateKey])) {
                    $unavailableStaffByDate[$dateKey][] = (int) $staffId;
                }

                $cursor->addDay();
            }
        }

        $staffs = User::whereIn('role', ['staff'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $prevMonth = $current->copy()->subMonth();
        $nextMonth = $current->copy()->addMonth();

        return view('admin.hr.schedules_index', [
            'staffs' => $staffs,
            'schedulesByDate' => $schedulesByDate,
            'leaveNamesByDate' => $leaveNamesByDate,
            'unavailableStaffByDate' => $unavailableStaffByDate,
            'current' => $current,
            'startOfMonth' => $startOfMonth,
            'endOfMonth' => $endOfMonth,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'today' => $today,
            'selectedStaffId' => $selectedStaffId,
        ]);
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

    // Tạo lịch làm việc cho nhân viên (phân công theo ngày)
    public function schedulesStore(Request $request)
    {
        $data = $request->validate([
            'staff_id' => ['required', 'exists:users,id'],
            'work_date' => ['required', 'date'],
            'shift_type' => ['required', 'in:morning,afternoon,fullday'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $workDate = Carbon::parse($data['work_date']);

        // Không phân công nếu nhân viên đang có đơn nghỉ phép đã duyệt trong ngày đó
        $hasApprovedLeave = LeaveRequest::where('staff_id', $data['staff_id'])
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $workDate->toDateString())
            ->whereDate('end_date', '>=', $workDate->toDateString())
            ->exists();

        if ($hasApprovedLeave) {
            return back()
                ->withInput()
                ->withErrors([
                    'staff_id' => 'Nhân viên này đang có đơn nghỉ phép đã được duyệt trong ngày chọn, không thể phân công.',
                ]);
        }

        // Không phân công vượt quá 5 ngày làm việc (thứ 2 - thứ 6) trong cùng tuần
        $weekStart = $workDate->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $workDate->copy()->endOfWeek(Carbon::SUNDAY);

        $weekSchedules = WorkSchedule::where('staff_id', $data['staff_id'])
            ->whereBetween('work_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get();

        $workingDaysCount = $weekSchedules->filter(function ($schedule) {
            return $schedule->work_date && !$schedule->work_date->isWeekend();
        })->count();

        if ($workingDaysCount >= 5) {
            return back()
                ->withInput()
                ->withErrors([
                    'staff_id' => 'Nhân viên này đã được phân công đủ số ngày làm việc trong tuần, không thể phân công thêm.',
                ]);
        }

        // Nếu chưa nhập giờ nhưng đã chọn ca, tự động gán giờ mặc định cho ca đó
        if (empty($data['start_time']) || empty($data['end_time'])) {
            switch ($data['shift_type']) {
                case 'morning':
                    $data['start_time'] = $data['start_time'] ?? '09:00';
                    $data['end_time'] = $data['end_time'] ?? '12:00';
                    break;
                case 'afternoon':
                    $data['start_time'] = $data['start_time'] ?? '13:30';
                    $data['end_time'] = $data['end_time'] ?? '18:00';
                    break;
                case 'fullday':
                    $data['start_time'] = $data['start_time'] ?? '09:00';
                    $data['end_time'] = $data['end_time'] ?? '18:00';
                    break;
            }
        }

        $startTime = isset($data['start_time']) ? Carbon::createFromFormat('Y-m-d H:i', $workDate->toDateString() . ' ' . $data['start_time']) : null;
        $endTime = isset($data['end_time']) ? Carbon::createFromFormat('Y-m-d H:i', $workDate->toDateString() . ' ' . $data['end_time']) : null;

        WorkSchedule::create([
            'staff_id' => $data['staff_id'],
            'manager_id' => Auth::id(),
            'work_date' => $workDate->toDateString(),
            'shift_type' => $data['shift_type'] ?? null,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'assigned',
            'note' => $data['note'] ?? null,
        ]);

        return redirect()
            ->route('admin.hr.schedules.index', [
                'month' => $workDate->month,
                'year' => $workDate->year,
                'staff_id' => $request->input('staff_id_filter'),
            ])
            ->with('success', 'Đã phân công lịch làm việc cho nhân viên.');
    }

    // Chấm công nhân viên (xem danh sách chấm công)
    public function attendancesIndex(Request $request)
    {
        // Mặc định xem và chấm công cho 1 ngày (default: hôm nay)
        $date = $request->filled('date') ? Carbon::parse($request->input('date'))->toDateString() : Carbon::today()->toDateString();

        // Danh sách nhân viên cần chấm công
        $staffQuery = User::where('role', 'staff')
            ->where('status', 'active');

        if ($request->filled('staff_id')) {
            $staffQuery->where('id', $request->staff_id);
        }

        $staffs = $staffQuery->orderBy('name')->get();

        // Lấy bản ghi chấm công (nếu đã có) cho ngày đó, theo từng nhân viên
        $attendances = Attendance::with(['staff', 'schedule'])
            ->whereDate('work_date', $date)
            ->whereIn('staff_id', $staffs->pluck('id'))
            ->get()
            ->keyBy('staff_id');

        return view('admin.hr.attendances_index', [
            'staffs' => $staffs,
            'attendances' => $attendances,
            'date' => $date,
        ]);
    }

    // HR/Quản lý chấm công ra cho nhân viên
    public function attendanceCheckOut(Request $request, Attendance $attendance)
    {
        $today = Carbon::today()->toDateString();

        if ($attendance->work_date->toDateString() !== $today) {
            return back()->with('info', 'Chỉ được phép chấm công cho ngày hiện tại.');
        }

        if ($attendance->check_out_time) {
            return back()->with('info', 'Đã chấm công ra cho nhân viên này.');
        }

        // Xác định ca chuẩn và kiểm tra về sớm
        $defaultEndTime = '18:00';
        $earlyLeaveGraceMinutes = 15;

        $attendance->loadMissing('schedule');
        $workDate = $attendance->work_date;
        $workDateString = $workDate->toDateString();

        if ($attendance->schedule && $attendance->schedule->end_time) {
            $expectedEnd = $attendance->schedule->end_time->copy()->setDate($workDate->year, $workDate->month, $workDate->day);
        } else {
            $expectedEnd = Carbon::createFromFormat('Y-m-d H:i', $workDateString . ' ' . $defaultEndTime);
        }

        $now = Carbon::now();
        $isEarlyLeave = $now->lt($expectedEnd->copy()->subMinutes($earlyLeaveGraceMinutes));

        // Không bắt buộc nhập lý do khi về sớm nữa
        $attendance->update([
            'check_out_time' => $now,
            'source' => 'manual',
        ]);

        return back()->with('success', 'Đã chấm công ra cho nhân viên.');
    }

    // HR tạo/chấm công vào cho nhân viên theo ngày (khi chưa có bản ghi)
    public function attendanceCheckInForStaff(Request $request)
    {
        $data = $request->validate([
            'staff_id' => ['required', 'exists:users,id'],
            'work_date' => ['required', 'date'],
        ]);

        $today = Carbon::today()->toDateString();
        $workDate = Carbon::parse($data['work_date']);

        if ($workDate->toDateString() !== $today) {
            return back()->with('info', 'Chỉ được phép chấm công cho ngày hiện tại.');
        }

        $attendance = Attendance::firstOrCreate(
            [
                'staff_id' => $data['staff_id'],
                'work_date' => $data['work_date'],
            ],
            [
                'status' => 'present',
                'source' => 'manual',
            ]
        );

        if ($attendance->check_in_time) {
            return back()->with('info', 'Đã chấm công vào cho nhân viên này.');
        }

        // Xác định ca chuẩn và kiểm tra trễ
        $defaultStartTime = '09:00';
        $lateGraceMinutes = 15;

        $schedule = WorkSchedule::where('staff_id', $data['staff_id'])
            ->whereDate('work_date', $workDate->toDateString())
            ->first();

        if ($schedule && $schedule->start_time) {
            $expectedStart = $schedule->start_time->copy()->setDate($workDate->year, $workDate->month, $workDate->day);
        } else {
            $expectedStart = Carbon::createFromFormat('Y-m-d H:i', $workDate->toDateString() . ' ' . $defaultStartTime);
        }

        $now = Carbon::now();
        $isLate = $now->gt($expectedStart->copy()->addMinutes($lateGraceMinutes));

        // Không bắt buộc và không còn lưu ghi chú khi đi trễ
        $attendance->update([
            'check_in_time' => $now,
            'status' => $attendance->status ?: 'present',
            'source' => 'manual',
        ]);

        return back()->with('success', 'Đã chấm công vào cho nhân viên.');
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
    public function mySchedules(Request $request)
    {
        $userId = Auth::id();

        $today = Carbon::today();
        $month = (int) $request->input('month', $today->month);
        $year = (int) $request->input('year', $today->year);

        $current = Carbon::create($year, $month, 1);
        $startOfMonth = $current->copy()->startOfMonth();
        $endOfMonth = $current->copy()->endOfMonth();

        $schedules = WorkSchedule::where('staff_id', $userId)
            ->whereBetween('work_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->orderBy('work_date')
            ->get()
            ->groupBy(function ($item) {
                return $item->work_date->toDateString();
            });

        $prevMonth = $current->copy()->subMonth();
        $nextMonth = $current->copy()->addMonth();

        return view('admin.staff_hr.my_schedules', [
            'schedulesByDate' => $schedules,
            'current' => $current,
            'startOfMonth' => $startOfMonth,
            'endOfMonth' => $endOfMonth,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'today' => $today,
        ]);
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

    // Bảng chấm công của tôi (dạng lịch + tự chấm công)
    public function myAttendances(Request $request)
    {
        $userId = Auth::id();

        $today = Carbon::today();
        $month = (int) $request->input('month', $today->month);
        $year = (int) $request->input('year', $today->year);

        $current = Carbon::create($year, $month, 1);
        $startOfMonth = $current->copy()->startOfMonth();
        $endOfMonth = $current->copy()->endOfMonth();

        $attendances = Attendance::where('staff_id', $userId)
            ->whereBetween('work_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(function ($item) {
                return $item->work_date->toDateString();
            });

        $schedules = WorkSchedule::where('staff_id', $userId)
            ->whereBetween('work_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(function ($item) {
                return $item->work_date->toDateString();
            });

        // Giả định & ràng buộc giờ làm chuẩn nếu không có lịch chi tiết
        $defaultStartTime = '09:00'; // giờ bắt đầu mặc định
        $defaultEndTime = '18:00';   // giờ kết thúc mặc định
        $lateGraceMinutes = 15;      // cho phép trễ tối đa 15 phút
        $earlyLeaveGraceMinutes = 15; // về sớm hơn 15 phút được tính là về sớm

        $dayInfos = [];
        $cursor = $startOfMonth->copy();

        while ($cursor->lte($endOfMonth)) {
            $keyDate = $cursor->toDateString();
            $attendance = $attendances->get($keyDate);
            $schedule = $schedules->get($keyDate);

            if ($schedule && $schedule->start_time) {
                $expectedStart = $schedule->start_time->copy()->setDate($cursor->year, $cursor->month, $cursor->day);
            } else {
                $expectedStart = Carbon::createFromFormat('Y-m-d H:i', $keyDate . ' ' . $defaultStartTime);
            }

            if ($schedule && $schedule->end_time) {
                $expectedEnd = $schedule->end_time->copy()->setDate($cursor->year, $cursor->month, $cursor->day);
            } else {
                $expectedEnd = Carbon::createFromFormat('Y-m-d H:i', $keyDate . ' ' . $defaultEndTime);
            }

            $statusLabel = null;
            $isLate = false;
            $isEarlyLeave = false;

            if ($attendance) {
                if ($attendance->check_in_time) {
                    $checkIn = $attendance->check_in_time;
                    if ($checkIn->gt($expectedStart->copy()->addMinutes($lateGraceMinutes))) {
                        $isLate = true;
                        $statusLabel = 'Đi trễ';
                    } else {
                        $statusLabel = 'Đúng giờ';
                    }
                } else {
                    $statusLabel = 'Chưa chấm vào';
                }

                if ($attendance->check_out_time) {
                    $checkOut = $attendance->check_out_time;
                    if ($checkOut->lt($expectedEnd->copy()->subMinutes($earlyLeaveGraceMinutes))) {
                        $isEarlyLeave = true;
                        $statusLabel = $statusLabel ? $statusLabel . ' / Về sớm' : 'Về sớm';
                    }
                }
            } else {
                if ($cursor->lt($today)) {
                    $statusLabel = 'Vắng (không chấm)';
                } else {
                    $statusLabel = 'Chưa chấm';
                }
            }

            $dayInfos[$keyDate] = [
                'attendance' => $attendance,
                'schedule' => $schedule,
                'expected_start' => $expectedStart,
                'expected_end' => $expectedEnd,
                'status_label' => $statusLabel,
                'is_late' => $isLate,
                'is_early_leave' => $isEarlyLeave,
            ];

            $cursor->addDay();
        }

        $prevMonth = $current->copy()->subMonth();
        $nextMonth = $current->copy()->addMonth();

        return view('admin.staff_hr.my_attendances', [
            'attendances' => $attendances,
            'dayInfos' => $dayInfos,
            'defaultStartTime' => $defaultStartTime,
            'defaultEndTime' => $defaultEndTime,
            'lateGraceMinutes' => $lateGraceMinutes,
            'earlyLeaveGraceMinutes' => $earlyLeaveGraceMinutes,
            'current' => $current,
            'startOfMonth' => $startOfMonth,
            'endOfMonth' => $endOfMonth,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'today' => $today,
        ]);
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
