@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Lịch làm việc của tôi</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="m-0 font-weight-bold text-primary">Lịch làm việc tháng {{ $current->format('m/Y') }}</h6>
                <small class="text-muted">Bạn xem được lịch làm việc đã được quản lý phân công.</small>
            </div>
            <div class="btn-group" role="group">
                <a href="{{ route('admin.staff-hr.schedules.index', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" class="btn btn-sm btn-outline-secondary">&laquo; Tháng trước</a>
                <a href="{{ route('admin.staff-hr.schedules.index', ['month' => $today->month, 'year' => $today->year]) }}" class="btn btn-sm btn-outline-primary">Tháng hiện tại</a>
                <a href="{{ route('admin.staff-hr.schedules.index', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" class="btn btn-sm btn-outline-secondary">Tháng sau &raquo;</a>
            </div>
        </div>
        <div class="card-body">
            @php
                $daysInMonth = $endOfMonth->day;
                $startWeekDay = $startOfMonth->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
                $weekDay = 1;
                $shiftTexts = [
                    'morning' => 'Ca sáng (09:00 - 12:00)',
                    'afternoon' => 'Ca chiều (13:30 - 18:00)',
                    'fullday' => 'Cả ngày (09:00 - 18:00)',
                ];
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
                                $isToday = $date->isSameDay($today);
                                $inCurrentMonth = $date->month === $current->month;
                            @endphp

                            <td class="p-1 {{ $isToday ? 'border-primary' : '' }} {{ ! $inCurrentMonth ? 'bg-light text-muted' : '' }}" style="min-width: 140px;">
                                <div class="font-weight-bold {{ $isToday ? 'text-primary' : '' }}">{{ $day }}</div>

                                @forelse ($daySchedules as $schedule)
                                    <div class="border rounded small mt-1 p-1 text-left">
                                        @php
                                            $shiftKey = $schedule->shift_type;
                                            $shiftLabel = $shiftTexts[$shiftKey] ?? $schedule->shift_type;
                                        @endphp
                                        @if ($shiftLabel)
                                            <div class="font-weight-bold">{{ $shiftLabel }}</div>
                                        @endif
                                        @if ($schedule->start_time && $schedule->end_time)
                                            <div>Giờ: {{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}</div>
                                        @endif
                                        @if ($schedule->manager)
                                            <div class="text-muted">Quản lý: {{ $schedule->manager->name }}</div>
                                        @endif
                                        @if ($schedule->note)
                                            <div class="text-muted">Ghi chú: {{ $schedule->note }}</div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="small text-muted mt-1">Không có lịch</div>
                                @endforelse
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

            <div class="mt-3 small text-muted">
                <strong>Lưu ý:</strong>
                <ul class="mb-0 pl-3">
                    <li>Lịch làm việc được sắp xếp và thay đổi bởi quản lý/nhân sự.</li>
                    <li>Ca làm được chuẩn hóa: sáng 09:00–12:00, chiều 13:30–18:00, cả ngày 09:00–18:00.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
