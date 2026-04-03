@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Bảng chấm công của tôi</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="m-0 font-weight-bold text-primary">Lịch chấm công tháng {{ $current->format('m/Y') }}</h6>
                <small class="text-muted">Bạn xem được lịch sử chấm công do quản lý ghi nhận.</small>
            </div>
            <div class="btn-group" role="group">
                <a href="{{ route('admin.staff-hr.attendances.index', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" class="btn btn-sm btn-outline-secondary">&laquo; Tháng trước</a>
                <a href="{{ route('admin.staff-hr.attendances.index', ['month' => $today->month, 'year' => $today->year]) }}" class="btn btn-sm btn-outline-primary">Hôm nay</a>
                <a href="{{ route('admin.staff-hr.attendances.index', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" class="btn btn-sm btn-outline-secondary">Tháng sau &raquo;</a>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('info'))
                <div class="alert alert-info">{{ session('info') }}</div>
            @endif

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
                                $info = $dayInfos[$dateString] ?? null;
                                $attendance = $info['attendance'] ?? null;
                                $schedule = $info['schedule'] ?? null;
                                $expectedStart = $info['expected_start'] ?? null;
                                $expectedEnd = $info['expected_end'] ?? null;
                                $statusLabel = $info['status_label'] ?? null;
                                $isLate = $info['is_late'] ?? false;
                                $isEarlyLeave = $info['is_early_leave'] ?? false;

                                $isToday = $date->isSameDay($today);
                                $inCurrentMonth = $date->month === $current->month;
                            @endphp

                            <td class="p-1 {{ $isToday ? 'border-primary' : '' }} {{ ! $inCurrentMonth ? 'bg-light text-muted' : '' }}">
                                <div class="font-weight-bold {{ $isToday ? 'text-primary' : '' }}">{{ $day }}</div>

                                @if ($expectedStart && $expectedEnd)
                                    <div class="small text-muted">
                                        Ca: {{ $expectedStart->format('H:i') }} - {{ $expectedEnd->format('H:i') }}
                                    </div>
                                @endif

                                @if ($attendance)
                                    <div class="small text-success">
                                        @if ($attendance->check_in_time)
                                            Vào: {{ $attendance->check_in_time->format('H:i') }}
                                        @endif
                                    </div>
                                    <div class="small text-danger">
                                        @if ($attendance->check_out_time)
                                            Ra: {{ $attendance->check_out_time->format('H:i') }}
                                        @endif
                                    </div>
                                @endif

                                @if ($statusLabel)
                                    @php
                                        $statusClass = 'text-muted';
                                        if ($isLate || $isEarlyLeave) {
                                            $statusClass = 'text-danger font-weight-bold';
                                        } elseif ($attendance && ! $isLate && ! $isEarlyLeave) {
                                            $statusClass = 'text-success';
                                        }
                                    @endphp
                                    <div class="small {{ $statusClass }}">{{ $statusLabel }}</div>
                                @endif

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
                <strong>Giả định & ràng buộc:</strong>
                <ul class="mb-0 pl-3">
                    <li>Giờ làm chuẩn nếu không có lịch ca: {{ $defaultStartTime }} - {{ $defaultEndTime }}.</li>
                    <li>Đi trễ khi chấm công vào sau {{ $lateGraceMinutes }} phút so với giờ bắt đầu ca.</li>
                    <li>Về sớm khi chấm công ra sớm hơn {{ $earlyLeaveGraceMinutes }} phút so với giờ kết thúc ca.</li>
                    <li>Ngày quá khứ không có chấm công được hiển thị là "Vắng (không chấm)".</li>
                    <li>Chấm công được thực hiện bởi quản lý/nhân sự, bạn chỉ xem kết quả.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
