@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Lịch làm việc nhân viên (Calendar)</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="m-0 font-weight-bold text-primary">Lịch làm việc tháng {{ $current->format('m/Y') }}</h6>
                <small class="text-muted">Phân công lịch làm việc theo dạng calendar, tự động loại nhân viên đang nghỉ phép.</small>
            </div>
            <div class="d-flex align-items-center">
                <form action="{{ route('admin.hr.schedules.index') }}" method="GET" class="form-inline mr-3">
                    <select name="staff_id" class="form-control form-control-sm mr-2">
                        <option value="">-- Tất cả nhân viên --</option>
                        @foreach ($staffs as $staff)
                            <option value="{{ $staff->id }}" {{ (int) $selectedStaffId === $staff->id ? 'selected' : '' }}>
                                {{ $staff->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Lọc</button>
                </form>
                <div class="btn-group" role="group">
                    <a href="{{ route('admin.hr.schedules.index', ['month' => $prevMonth->month, 'year' => $prevMonth->year, 'staff_id' => $selectedStaffId]) }}" class="btn btn-sm btn-outline-secondary">&laquo; Tháng trước</a>
                    <a href="{{ route('admin.hr.schedules.index', ['month' => $today->month, 'year' => $today->year, 'staff_id' => $selectedStaffId]) }}" class="btn btn-sm btn-outline-primary">Tháng hiện tại</a>
                    <a href="{{ route('admin.hr.schedules.index', ['month' => $nextMonth->month, 'year' => $nextMonth->year, 'staff_id' => $selectedStaffId]) }}" class="btn btn-sm btn-outline-secondary">Tháng sau &raquo;</a>
                </div>
            </div>
        </div>
        <div class="card-body">
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

            {{-- Form phân công lịch làm việc --}}
            <form action="{{ route('admin.hr.schedules.store') }}" method="POST" class="mb-3">
                @csrf
                <div class="form-row align-items-end">
                    <div class="col-md-4">
                        <label for="work_date">Ngày làm việc</label>
                        <input type="date" id="work_date" name="work_date" class="form-control form-control-sm" value="{{ old('work_date', $today->toDateString()) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="staff_id">Nhân viên</label>
                        <select id="staff_id" name="staff_id" class="form-control form-control-sm" required>
                            <option value="">-- Chọn nhân viên --</option>
                            @foreach ($staffs as $staff)
                                <option value="{{ $staff->id }}" {{ old('staff_id') == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="shift_type">Ca làm</label>
                        <select id="shift_type" name="shift_type" class="form-control form-control-sm" required>
                            <option value="">-- Chọn ca --</option>
                            <option value="morning" {{ old('shift_type') === 'morning' ? 'selected' : '' }}>Sáng (09:00 - 12:00)</option>
                            <option value="afternoon" {{ old('shift_type') === 'afternoon' ? 'selected' : '' }}>Chiều (13:30 - 18:00)</option>
                            <option value="fullday" {{ old('shift_type') === 'fullday' ? 'selected' : '' }}>Cả ngày (09:00 - 18:00)</option>
                        </select>
                    </div>
                </div>
                <div class="form-row mt-2">
                    <div class="col-md-8">
                        <label for="note">Ghi chú</label>
                        <input type="text" id="note" name="note" class="form-control form-control-sm" value="{{ old('note') }}" placeholder="Ghi chú thêm (nếu có)">
                    </div>
                    <div class="col-md-4 text-right">
                        <input type="hidden" name="staff_id_filter" value="{{ $selectedStaffId }}">
                        <button type="submit" class="btn btn-sm btn-primary mt-4">Phân công lịch làm việc</button>
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
                });
            </script>

            <div class="mt-3 small text-muted">
                <strong>Ghi chú:</strong>
                <ul class="mb-0 pl-3">
                    <li>Nhân viên có đơn nghỉ phép đã duyệt trong ngày sẽ không được phép phân công.</li>
                    <li>Nhân viên đã được phân công đủ 5 ngày làm việc (thứ 2 - thứ 6) trong tuần sẽ không được phân công thêm trong tuần đó.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
