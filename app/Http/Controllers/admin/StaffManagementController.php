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
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StaffManagementController extends Controller
{
    // Danh sách nhân viên kèm phòng ban
    public function staffIndex(Request $request)
    {
        $departments = Department::orderBy('name')->get();

        // Lấy tất cả nhân viên công ty (tài khoản nội bộ, không bao gồm khách/đối tác và admin)
        $query = User::with('department')
            ->whereIn('role', ['tour_manager', 'staff_manager', 'staff', 'tour_guide'])
            ->where('status', 'active');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('keyword')) {
            $keyword = '%' . trim($request->keyword) . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', $keyword)
                    ->orWhere('email', 'like', $keyword)
                    ->orWhere('phone', 'like', $keyword);
            });
        }

        $staffs = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.hr.staff_index', [
            'staffs' => $staffs,
            'departments' => $departments,
            'selectedDepartmentId' => $request->department_id,
            'keyword' => $request->keyword,
        ]);
    }

    // Phân công & xem lịch làm việc của nhân viên
    public function schedulesIndex(Request $request)
    {
        $today = Carbon::today();

        $month = (int) $request->input('month', $today->month);
        $year = (int) $request->input('year', $today->year);
        $selectedStaffId = $request->input('staff_id');
        $selectedDepartmentId = $request->input('department_id');

        $current = Carbon::create($year, $month, 1); // ngày 1 của tháng được chọn
        $startOfMonth = $current->copy()->startOfMonth(); // dùng copy() để tránh thay đổi $current
        $endOfMonth = $current->copy()->endOfMonth();

        $scheduleQuery = WorkSchedule::with(['staff', 'manager'])
            ->whereBetween('work_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->orderBy('work_date');

        if (!empty($selectedStaffId)) {
            $scheduleQuery->where('staff_id', $selectedStaffId);
        }

        if (!empty($selectedDepartmentId)) {
            $scheduleQuery->whereHas('staff', function ($q) use ($selectedDepartmentId) {
                $q->where('department_id', $selectedDepartmentId);
            });
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

        // Lấy danh sách nhân viên nội bộ giống logic trang danh sách nhân viên
        $staffQuery = User::whereIn('role', ['tour_manager', 'staff_manager', 'staff', 'tour_guide'])
            ->where('status', 'active');

        if (!empty($selectedDepartmentId)) {
            $staffQuery->where('department_id', $selectedDepartmentId);
        }

        $staffs = $staffQuery->orderBy('name')->get();

        $departments = Department::orderBy('name')->get();

        $prevMonth = $current->copy()->subMonth();
        $nextMonth = $current->copy()->addMonth();

        return view('admin.hr.schedules_index', [
            'staffs' => $staffs,
            'departments' => $departments,
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
            'selectedDepartmentId' => $selectedDepartmentId,
        ]);
    }

    // Danh sách & duyệt đơn nghỉ phép
    public function leavesIndex(Request $request)
    {
        $query = LeaveRequest::with(['staff.department', 'manager'])->orderByDesc('created_at');

        // Lọc theo phòng ban (thông qua nhân viên)
        if ($request->filled('department_id')) {
            $departmentId = (int) $request->department_id;
            $query->whereHas('staff', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        // Lọc theo loại nghỉ
        if ($request->filled('leave_type') && $request->leave_type !== 'all') {
            $query->where('leave_type', $request->leave_type);
        }

        // Lọc theo khoảng thời gian (ngày bắt đầu/kết thúc của đơn nghỉ)
        if ($request->filled('from_date')) {
            $from = Carbon::parse($request->from_date)->toDateString();
            $query->whereDate('start_date', '>=', $from);
        }

        if ($request->filled('to_date')) {
            $to = Carbon::parse($request->to_date)->toDateString();
            $query->whereDate('end_date', '<=', $to);
        }

        $leaves = $query->paginate(15)->appends($request->query());

        // Dữ liệu phục vụ bộ lọc
        $departments = Department::orderBy('name')->get();
        $leaveTypes = LeaveRequest::select('leave_type')
            ->whereNotNull('leave_type')
            ->distinct()
            ->orderBy('leave_type')
            ->pluck('leave_type');

        return view('admin.hr.leaves_index', [
            'leaves' => $leaves,
            'departments' => $departments,
            'leaveTypes' => $leaveTypes,
            'filters' => [
                'leave_type' => $request->input('leave_type', 'all'),
                'department_id' => $request->input('department_id'),
                'from_date' => $request->input('from_date'),
                'to_date' => $request->input('to_date'),
            ],
        ]);
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

    // Tạo lịch làm việc cho nhân viên (phân công theo ngày, cho phép chọn nhiều ngày + ca tương ứng)
    public function schedulesStore(Request $request)
    {
        $data = $request->validate([
            'staff_id' => ['required', 'exists:users,id'],
            // giữ cho tương thích cũ (1 ngày + 1 ca)
            'work_date' => ['nullable', 'date'],
            'shift_type' => ['nullable', 'in:morning,afternoon,fullday'],
            // cấu trúc mới: nhiều ngày + ca tương ứng
            'work_dates' => ['nullable', 'array'],
            'work_dates.*' => ['nullable', 'date'],
            'shift_types' => ['nullable', 'array'],
            'shift_types.*' => ['nullable', 'in:morning,afternoon,fullday'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        // Gom danh sách (ngày, ca) cần phân công
        $workItems = [];

        if (!empty($data['work_dates']) && is_array($data['work_dates'])) {
            $dates = $data['work_dates'];
            $shifts = $data['shift_types'] ?? [];

            foreach ($dates as $index => $dateStr) {
                $dateStr = $dateStr ?? null;
                $shift = $shifts[$index] ?? null;

                if (!empty($dateStr) && !empty($shift)) {
                    $workItems[] = [
                        'date' => $dateStr,
                        'shift_type' => $shift,
                    ];
                }
            }
        } elseif (!empty($data['work_date']) && !empty($data['shift_type'])) {
            // fallback: 1 ngày + 1 ca cũ
            $workItems[] = [
                'date' => $data['work_date'],
                'shift_type' => $data['shift_type'],
            ];
        }

        if (empty($workItems)) {
            return back()
                ->withInput()
                ->withErrors([
                    'work_dates' => 'Vui lòng nhập ít nhất một dòng Ngày làm việc và Ca làm.',
                ]);
        }

        $createdCount = 0;
        $skipMessages = [];
        $firstWorkDate = null;
        $weekBaseCounts = [];
        $weekExtraCounts = [];

        foreach ($workItems as $item) {
            $workDate = Carbon::parse($item['date']);
            $shiftType = $item['shift_type'];

            if ($firstWorkDate === null) {
                $firstWorkDate = $workDate->copy();
            }

            // Không phân công nếu nhân viên đang có đơn nghỉ phép đã duyệt trong ngày đó
            $hasApprovedLeave = LeaveRequest::where('staff_id', $data['staff_id'])
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $workDate->toDateString())
                ->whereDate('end_date', '>=', $workDate->toDateString())
                ->exists();

            if ($hasApprovedLeave) {
                $skipMessages[] = 'Ngày ' . $workDate->format('d/m/Y') . ' nhân viên đang nghỉ phép, bỏ qua phân công.';
                continue;
            }

            // Không phân công vào cuối tuần (thứ 7, CN)
            if ($workDate->isWeekend()) {
                $skipMessages[] = 'Ngày ' . $workDate->format('d/m/Y') . ' là cuối tuần, bỏ qua phân công.';
                continue;
            }

            // Không phân công vượt quá 5 ngày làm việc (thứ 2 - thứ 6) trong cùng tuần
            $weekStart = $workDate->copy()->startOfWeek(Carbon::MONDAY);
            $weekKey = $data['staff_id'] . '|' . $weekStart->toDateString();

            if (!isset($weekBaseCounts[$weekKey])) {
                $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

                $weekSchedules = WorkSchedule::where('staff_id', $data['staff_id'])
                    ->whereBetween('work_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                    ->get();

                $weekBaseCounts[$weekKey] = $weekSchedules->filter(function ($schedule) {
                    return $schedule->work_date && !$schedule->work_date->isWeekend();
                })->count();

                $weekExtraCounts[$weekKey] = 0;
            }

            $currentCount = $weekBaseCounts[$weekKey] + ($weekExtraCounts[$weekKey] ?? 0);

            if ($currentCount >= 5) {
                $skipMessages[] = 'Tuần chứa ngày ' . $workDate->format('d/m/Y') . ' nhân viên đã đủ số ngày làm việc, bỏ qua ngày này.';
                continue;
            }

            // Xác định giờ bắt đầu/kết thúc cho ngày hiện tại
            $startTimeStr = $data['start_time'] ?? null;
            $endTimeStr = $data['end_time'] ?? null;

            // Nếu chưa nhập giờ nhưng đã chọn ca, tự động gán giờ mặc định cho ca đó
            if (empty($startTimeStr) || empty($endTimeStr)) {
                switch ($shiftType) {
                    case 'morning':
                        $startTimeStr = $startTimeStr ?? '09:00';
                        $endTimeStr = $endTimeStr ?? '12:00';
                        break;
                    case 'afternoon':
                        $startTimeStr = $startTimeStr ?? '13:30';
                        $endTimeStr = $endTimeStr ?? '18:00';
                        break;
                    case 'fullday':
                        $startTimeStr = $startTimeStr ?? '09:00';
                        $endTimeStr = $endTimeStr ?? '18:00';
                        break;
                }
            }

            $startTime = $startTimeStr
                ? Carbon::createFromFormat('Y-m-d H:i', $workDate->toDateString() . ' ' . $startTimeStr)
                : null;
            $endTime = $endTimeStr
                ? Carbon::createFromFormat('Y-m-d H:i', $workDate->toDateString() . ' ' . $endTimeStr)
                : null;

            WorkSchedule::create([
                'staff_id' => $data['staff_id'],
                'manager_id' => Auth::id(),
                'work_date' => $workDate->toDateString(),
                'shift_type' => $shiftType,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 'assigned',
                'note' => $data['note'] ?? null,
            ]);

            $weekExtraCounts[$weekKey] = ($weekExtraCounts[$weekKey] ?? 0) + 1;

            $createdCount++;
        }

        if ($createdCount === 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'staff_id' => 'Không thể phân công lịch làm việc cho bất kỳ ngày nào. Vui lòng kiểm tra lại giới hạn ngày làm việc trong tuần và đơn nghỉ phép.',
                ]);
        }

        $redirectDate = $firstWorkDate ?? Carbon::today();

        $message = 'Đã phân công lịch làm việc cho nhân viên (' . $createdCount . ' ngày).';
        if (!empty($skipMessages)) {
            $message .= ' Một số ngày bị bỏ qua: ' . implode(' ', $skipMessages);
        }

        return redirect()
            ->route('admin.hr.schedules.index', [
                'month' => $redirectDate->month,
                'year' => $redirectDate->year,
                'staff_id' => $request->input('staff_id_filter'),
                'department_id' => $request->input('department_id_filter'),
            ])
            ->with('success', $message);
    }

    // Sao chép lịch làm việc của 1 tuần sang tuần kế tiếp (theo phòng ban tuỳ chọn)
    public function schedulesCopyWeek(Request $request)
    {
        $data = $request->validate([
            'week_start' => ['required', 'date'],
            'department_id' => ['nullable', 'exists:departments,id'],
        ]);

        $weekStart = Carbon::parse($data['week_start'])->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        $nextWeekStart = $weekStart->copy()->addWeek();
        $nextWeekEnd = $nextWeekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $query = WorkSchedule::with('staff')
            ->whereBetween('work_date', [$weekStart->toDateString(), $weekEnd->toDateString()]);

        if (!empty($data['department_id'])) {
            $departmentId = $data['department_id'];
            $query->whereHas('staff', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $sourceSchedules = $query->get();

        $weekBaseCounts = [];
        $weekExtraCounts = [];
        $copiedCount = 0;

        foreach ($sourceSchedules as $schedule) {
            if (!$schedule->work_date) {
                continue;
            }

            $targetDate = $schedule->work_date->copy()->addWeek();

            // Không sao chép nếu ra ngoài tuần kế tiếp (phòng hờ trường hợp dữ liệu lệch)
            if ($targetDate->lt($nextWeekStart) || $targetDate->gt($nextWeekEnd)) {
                continue;
            }

            // Không sao chép lịch vào cuối tuần
            if ($targetDate->isWeekend()) {
                continue;
            }

            // Không sao chép nếu nhân viên nghỉ phép ở ngày mới
            $hasApprovedLeave = LeaveRequest::where('staff_id', $schedule->staff_id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $targetDate->toDateString())
                ->whereDate('end_date', '>=', $targetDate->toDateString())
                ->exists();

            if ($hasApprovedLeave) {
                continue;
            }

            // Kiểm tra giới hạn 5 ngày làm việc trong tuần cho ngày mới (tính cả các lịch mới được sao chép trong request này)
            $weekStartForCheck = $targetDate->copy()->startOfWeek(Carbon::MONDAY);
            $weekKey = $schedule->staff_id . '|' . $weekStartForCheck->toDateString();

            if (!isset($weekBaseCounts[$weekKey])) {
                $weekEndForCheck = $weekStartForCheck->copy()->endOfWeek(Carbon::SUNDAY);

                $weekSchedules = WorkSchedule::where('staff_id', $schedule->staff_id)
                    ->whereBetween('work_date', [$weekStartForCheck->toDateString(), $weekEndForCheck->toDateString()])
                    ->get();

                $weekBaseCounts[$weekKey] = $weekSchedules->filter(function ($item) {
                    return $item->work_date && !$item->work_date->isWeekend();
                })->count();

                $weekExtraCounts[$weekKey] = 0;
            }

            $currentCount = $weekBaseCounts[$weekKey] + ($weekExtraCounts[$weekKey] ?? 0);

            if ($currentCount >= 5) {
                continue;
            }

            // Không tạo trùng lịch cũ nếu đã tồn tại cho nhân viên + ngày + ca
            $exists = WorkSchedule::where('staff_id', $schedule->staff_id)
                ->whereDate('work_date', $targetDate->toDateString())
                ->where('shift_type', $schedule->shift_type)
                ->exists();

            if ($exists) {
                continue;
            }

            WorkSchedule::create([
                'staff_id' => $schedule->staff_id,
                'manager_id' => Auth::id(),
                'work_date' => $targetDate->toDateString(),
                'shift_type' => $schedule->shift_type,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'status' => 'assigned',
                'note' => $schedule->note,
            ]);

            $weekExtraCounts[$weekKey] = ($weekExtraCounts[$weekKey] ?? 0) + 1;
            $copiedCount++;
        }

        $message = 'Đã sao chép lịch làm việc sang tuần kế tiếp.';
        if ($copiedCount === 0) {
            $message = 'Không có lịch nào được sao chép sang tuần kế tiếp (có thể do nghỉ phép hoặc đã đủ số ngày làm việc).';
        }

        return redirect()->route('admin.hr.schedules.index', [
            'month' => $nextWeekStart->month,
            'year' => $nextWeekStart->year,
            'staff_id' => $request->input('staff_id_filter'),
            'department_id' => $request->input('department_id_filter', $data['department_id'] ?? null),
        ])->with('success', $message);
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
    // public function payrollsIndex()
    // {
    //     $periods = PayrollPeriod::with(['items.staff'])
    //         ->orderByDesc('start_date')
    //         ->paginate(10);

    //     return view('admin.hr.payrolls_index', compact('periods'));
    // }

    // Báo cáo công việc nhân viên
    public function reportsIndex(Request $request)
    {
        $query = WorkReport::with(['staff.department', 'manager'])
            ->orderByDesc('report_date');

        if ($request->filled('department_id')) {
            $departmentId = $request->department_id;
            $query->whereHas('staff', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        $reports = $query->paginate(20)->appends($request->only(['department_id', 'staff_id']));

        $staffQuery = User::where('role', 'staff')
            ->where('status', 'active');

        if ($request->filled('department_id')) {
            $staffQuery->where('department_id', $request->department_id);
        }

        $staffs = $staffQuery->orderBy('name')->get();

        $departments = Department::where('status', 'active')->orderBy('name')->get();

        return view('admin.hr.reports_index', [
            'reports' => $reports,
            'departments' => $departments,
            'staffs' => $staffs,
        ]);
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
            'report_file' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,jpg,jpeg,png', 'max:10240'],
            'content' => ['nullable', 'string'],
            'total_tasks' => ['nullable', 'integer', 'min:0'],
            'total_hours' => ['nullable', 'numeric', 'min:0'],
        ]);

        // Lưu file báo cáo lên storage (disk public)
        if ($request->hasFile('report_file')) {
            $path = $request->file('report_file')->store('work_reports', 'public');
            $data['file_path'] = $path;
        }

        $data['staff_id'] = Auth::id();
        $data['status'] = 'submitted';

        // Với cấu trúc CSDL hiện tại, cột content không cho phép null
        // nên đảm bảo luôn có giá trị (có thể là ghi chú hoặc chuỗi rỗng)
        if (!isset($data['content'])) {
            $data['content'] = '';
        }

        WorkReport::create($data);

        return redirect()->route('admin.staff-hr.reports.index')
            ->with('success', 'Đã gửi báo cáo công việc.');
    }
}
