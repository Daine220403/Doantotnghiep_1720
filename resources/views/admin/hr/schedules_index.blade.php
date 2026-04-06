@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Lịch làm việc nhân viên (Calendar)</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="m-0 font-weight-bold text-primary">Lịch làm việc tháng {{ $current->format('m/Y') }}</h6>
                <small class="text-muted">Phân công lịch làm theo từng bước: chọn phòng ban → nhân viên → ngày / ca.</small>
            </div>
            <div class="btn-group" role="group">
                <a href="{{ route('admin.hr.schedules.index', ['month' => $prevMonth->month, 'year' => $prevMonth->year, 'staff_id' => $selectedStaffId, 'department_id' => $selectedDepartmentId]) }}" class="btn btn-sm btn-outline-secondary">&laquo; Tháng trước</a>
                <a href="{{ route('admin.hr.schedules.index', ['month' => $today->month, 'year' => $today->year, 'staff_id' => $selectedStaffId, 'department_id' => $selectedDepartmentId]) }}" class="btn btn-sm btn-outline-primary">Tháng hiện tại</a>
                <a href="{{ route('admin.hr.schedules.index', ['month' => $nextMonth->month, 'year' => $nextMonth->year, 'staff_id' => $selectedStaffId, 'department_id' => $selectedDepartmentId]) }}" class="btn btn-sm btn-outline-secondary">Tháng sau &raquo;</a>
            </div>
        </div>
        <div class="card-body">
            {{-- Bước 1: Chọn phòng ban để lọc danh sách nhân viên --}}
            <form action="{{ route('admin.hr.schedules.index') }}" method="GET" class="mb-3 border rounded p-2 bg-light">
                <div class="form-row align-items-end">
                    <div class="col-md-6">
                        <label class="mb-1 font-weight-bold" for="filter_department_id">Bước 1: Chọn phòng ban</label>
                        <select id="filter_department_id" name="department_id" class="form-control form-control-sm">
                            <option value="">-- Tất cả phòng ban --</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" {{ (int) $selectedDepartmentId === $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mt-3 mt-md-0">
                        <label class="mb-1 d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Lọc theo phòng ban</button>
                    </div>
                </div>
            </form>
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Bước 2-3: Chọn nhân viên, nhiều ngày làm kèm ca làm rồi phân công --}}
            <form action="{{ route('admin.hr.schedules.store') }}" method="POST" class="mb-3">
                @csrf
                <div class="form-row align-items-end">
                    <div class="col-md-12">
                        <label for="staff_id" class="font-weight-bold">Bước 2: Chọn nhân viên</label>
                        <select id="staff_id" name="staff_id" class="form-control form-control-sm" required>
                            <option value="">-- Chọn nhân viên --</option>
                            @foreach ($staffs as $staff)
                                <option value="{{ $staff->id }}" {{ old('staff_id') == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12 mt-3">
                        <label class="font-weight-bold">Bước 3: Ngày làm việc &amp; ca làm (có thể chọn nhiều)</label>
                        @php
                            $oldWorkDates = old('work_dates', []);
                            $oldShiftTypes = old('shift_types', []);
                            // Ngày mặc định: thứ 2 của tuần kế tiếp so với hôm nay
                            $nextWeekFirstWorkDate = $today->copy()->addWeek()->startOfWeek()->toDateString();
                            $nextWeekLastAllowedDate = $today->copy()->addWeek()->startOfWeek()->addDays(4)->toDateString(); // Thứ 6 tuần kế tiếp
                        @endphp
                        <div id="work_dates_wrapper">
                            @if (!empty($oldWorkDates))
                                @foreach ($oldWorkDates as $index => $oldDate)
                                    @php
                                        $oldShift = $oldShiftTypes[$index] ?? null;
                                    @endphp
                                    <div class="input-group input-group-sm mb-1 work-date-item">
                                        <input type="date" @if ($index === 0) id="work_date" @endif name="work_dates[]" class="form-control" value="{{ $oldDate }}" required>
                                        <select name="shift_types[]" class="custom-select" required>
                                            <option value="">-- Ca --</option>
                                            <option value="morning" {{ $oldShift === 'morning' ? 'selected' : '' }}>Sáng (09:00 - 12:00)</option>
                                            <option value="afternoon" {{ $oldShift === 'afternoon' ? 'selected' : '' }}>Chiều (13:30 - 18:00)</option>
                                            <option value="fullday" {{ $oldShift === 'fullday' ? 'selected' : '' }}>Cả ngày (09:00 - 18:00)</option>
                                        </select>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-work-date" @if ($index === 0) style="display: none;" @endif>-</button>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="input-group input-group-sm mb-1 work-date-item">
                                    <input type="date" id="work_date" name="work_dates[]" class="form-control" value="{{ $nextWeekFirstWorkDate }}" required>
                                    <select name="shift_types[]" class="custom-select" required>
                                        <option value="">-- Ca --</option>
                                        <option value="morning">Sáng (09:00 - 12:00)</option>
                                        <option value="afternoon">Chiều (13:30 - 18:00)</option>
                                        <option value="fullday">Cả ngày (09:00 - 18:00)</option>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-work-date" style="display: none;">-</button>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <button type="button" class="btn btn-link btn-sm p-0 mt-1" id="add_work_date">+ Thêm dòng Ngày &amp; Ca làm</button>
                        <small class="text-muted d-block">Mỗi dòng là một ngày làm việc với ca tương ứng.</small>
                    </div>
                </div>
                <div class="form-row mt-2">
                    <div class="col-md-8">
                        <label for="note">Ghi chú</label>
                        <input type="text" id="note" name="note" class="form-control form-control-sm" value="{{ old('note') }}" placeholder="Ghi chú thêm (nếu có)">
                    </div>
                    <div class="col-md-4 text-right">
                        <input type="hidden" name="staff_id_filter" value="{{ $selectedStaffId }}">
                        <input type="hidden" name="department_id_filter" value="{{ $selectedDepartmentId }}">
                        <button type="submit" class="btn btn-sm btn-primary mt-4">Bước 5: Phân công lịch làm việc</button>
                    </div>
                </div>
            </form>

            @php
                $daysInMonth = $endOfMonth->day;
                $startWeekDay = $startOfMonth->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
                $weekDay = 1;
            @endphp

            <table class="table table-bordered table-sm text-center align-middle">
                <thead class="thead-light">
                    <tr>
                        <th>Thứ 2</th>
                        <th>Thứ 3</th>
                        <th>Thứ 4</th>
                        <th>Thứ 5</th>
                        <th>Thứ 6</th>
                        <th>Thứ 7</th>
                        <th>Chủ nhật</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @for ($i = 1; $i < $startWeekDay; $i++)
                            <td class="bg-light"></td>
                            @php $weekDay++; @endphp
                        @endfor

                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $date = $startOfMonth->copy()->day($day);
                                $dateString = $date->toDateString();
                                $daySchedules = $schedulesByDate[$dateString] ?? collect();
                                $leaveNames = $leaveNamesByDate[$dateString] ?? [];
                                $isToday = $date->isSameDay($today);
                                $inCurrentMonth = $date->month === $current->month;
                            @endphp

                            <td class="p-1 {{ $isToday ? 'border-primary' : '' }} {{ ! $inCurrentMonth ? 'bg-light text-muted' : '' }}" style="min-width: 140px;">
                                <div class="font-weight-bold {{ $isToday ? 'text-primary' : '' }}">{{ $day }}</div>

                                @if ($leaveNames)
                                    <div class="small text-danger mt-1">
                                        <strong>Nghỉ:</strong> {{ implode(', ', array_unique($leaveNames)) }}
                                    </div>
                                @endif

                                @foreach ($daySchedules as $schedule)
                                    <div class="border rounded small mt-1 p-1 text-left">
                                        <div class="font-weight-bold">{{ optional($schedule->staff)->name }}</div>
                                        @if ($schedule->shift_type)
                                            <div>Ca: {{ $schedule->shift_type }}</div>
                                        @endif
                                        @if ($schedule->start_time && $schedule->end_time)
                                            <div>Giờ: {{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}</div>
                                        @endif
                                        @if ($schedule->note)
                                            <div class="text-muted">{{ $schedule->note }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </td>

                            @if ($weekDay % 7 == 0)
                                </tr><tr>
                            @endif

                            @php $weekDay++; @endphp
                        @endfor

                        @while (($weekDay - 1) % 7 != 0)
                            <td class="bg-light"></td>
                            @php $weekDay++; @endphp
                        @endwhile
                    </tr>
                </tbody>
            </table>

            {{-- Sao chép lịch đã có của 1 tuần sang tuần kế tiếp --}}
            <form action="{{ route('admin.hr.schedules.copy-week') }}" method="POST" class="mt-3 mb-3 border rounded p-2 bg-light">
                @csrf
                <div class="form-row align-items-end">
                    <div class="col-md-3">
                        <label for="copy_week_start">Sao chép tuần bắt đầu</label>
                        <input type="date" id="copy_week_start" name="week_start" class="form-control form-control-sm" value="{{ $today->copy()->startOfWeek()->toDateString() }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="copy_department_id">Phòng ban (tùy chọn)</label>
                        <select id="copy_department_id" name="department_id" class="form-control form-control-sm">
                            <option value="">-- Tất cả phòng ban --</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" {{ (int) $selectedDepartmentId === $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5 text-right">
                        <input type="hidden" name="staff_id_filter" value="{{ $selectedStaffId }}">
                        <input type="hidden" name="department_id_filter" value="{{ $selectedDepartmentId }}">
                        <button type="submit" class="btn btn-sm btn-outline-primary mt-4">Sao chép lịch tuần sang tuần sau</button>
                    </div>
                </div>
            </form>

            @php
                // Chuẩn bị dữ liệu JSON cho việc ẩn nhân viên không thể phân công theo ngày
                $unavailableMap = [];
                foreach ($unavailableStaffByDate as $dateKey => $staffIds) {
                    $unavailableMap[$dateKey] = array_values(array_unique(array_map('intval', $staffIds)));
                }
            @endphp

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var unavailableStaffByDate = @json($unavailableMap);
                    var dateInput = document.getElementById('work_date');
                    var staffSelect = document.getElementById('staff_id');
                    var workDatesWrapper = document.getElementById('work_dates_wrapper');
                    var addWorkDateBtn = document.getElementById('add_work_date');
                    var defaultNextWeekStart = '{{ $nextWeekFirstWorkDate ?? $today->copy()->addWeek()->startOfWeek()->toDateString() }}';
                    var limitWeekEnd = '{{ $nextWeekLastAllowedDate ?? $today->copy()->addWeek()->startOfWeek()->addDays(4)->toDateString() }}';

                    function refreshStaffOptions() {
                        if (!dateInput || !staffSelect) {
                            return;
                        }

                        var dateValue = dateInput.value;
                        var unavailable = unavailableStaffByDate[dateValue] || [];

                        Array.prototype.forEach.call(staffSelect.options, function (option) {
                            if (!option.value) {
                                return;
                            }

                            var staffId = parseInt(option.value, 10);
                            if (unavailable.indexOf(staffId) !== -1) {
                                option.disabled = true;
                                option.classList.add('d-none');
                            } else {
                                option.disabled = false;
                                option.classList.remove('d-none');
                            }
                        });
                    }

                    if (dateInput) {
                        dateInput.addEventListener('change', refreshStaffOptions);
                    }

                    refreshStaffOptions();

                    // Thêm/bớt input ngày làm việc (Bước 3)
                    function getNextWorkingDate(prevDateString) {
                        if (!prevDateString) {
                            return defaultNextWeekStart;
                        }

                        var d = new Date(prevDateString);
                        if (isNaN(d.getTime())) {
                            return defaultNextWeekStart;
                        }

                        // cộng thêm 1 ngày, không nhảy sang tuần kế tiếp
                        d.setDate(d.getDate() + 1);

                        var limit = new Date(limitWeekEnd);
                        if (d.getTime() > limit.getTime()) {
                            return null; // đã vượt qua thứ 6 tuần kế tiếp
                        }

                        var year = d.getFullYear();
                        var month = (d.getMonth() + 1).toString().padStart(2, '0');
                        var day = d.getDate().toString().padStart(2, '0');
                        return year + '-' + month + '-' + day;
                    }

                    if (addWorkDateBtn && workDatesWrapper) {
                        addWorkDateBtn.addEventListener('click', function () {
                            var wrapper = workDatesWrapper;
                            var dateInputs = wrapper.querySelectorAll('input[type="date"][name="work_dates[]"]');
                            var lastDateValue = '';
                            if (dateInputs.length > 0) {
                                lastDateValue = dateInputs[dateInputs.length - 1].value;
                            }

                            var newDateValue = getNextWorkingDate(lastDateValue);
                            if (!newDateValue) {
                                alert('Chỉ được thêm ngày trong tuần kế tiếp (tối đa đến thứ 6).');
                                return;
                            }

                            var div = document.createElement('div');
                            div.className = 'input-group input-group-sm mb-1 work-date-item';
                            div.innerHTML =
                                '<input type="date" name="work_dates[]" class="form-control" value="' + newDateValue + '" required>' +
                                '<select name="shift_types[]" class="custom-select" required>' +
                                    '<option value="">-- Ca --</option>' +
                                    '<option value="morning">Sáng (09:00 - 12:00)</option>' +
                                    '<option value="afternoon">Chiều (13:30 - 18:00)</option>' +
                                    '<option value="fullday">Cả ngày (09:00 - 18:00)</option>' +
                                '</select>' +
                                '<div class="input-group-append">' +
                                    '<button type="button" class="btn btn-outline-danger btn-sm remove-work-date">-</button>' +
                                '</div>';
                            wrapper.appendChild(div);
                        });

                        workDatesWrapper.addEventListener('click', function (e) {
                            if (e.target.classList.contains('remove-work-date')) {
                                var item = e.target.closest('.work-date-item');
                                if (item) {
                                    item.remove();
                                }
                            }
                        });
                    }
                });
            </script>

            <div class="mt-2 small text-muted">
                <strong>Ghi chú:</strong>
                <ul class="mb-0 pl-3">
                    <li>Nhân viên có đơn nghỉ phép đã duyệt trong ngày sẽ không được phép phân công.</li>
                    <li>Nhân viên đã được phân công đủ 5 ngày làm việc (thứ 2 - thứ 6) trong tuần sẽ không được phân công thêm trong tuần đó.</li>
                    <li>Hệ thống không phân công lịch làm việc vào thứ 7 và Chủ nhật.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
