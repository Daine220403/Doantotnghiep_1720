@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Chấm công nhân viên</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách chấm công</h6>
            @isset($staffs)
                <form method="GET" class="form-inline">
                    <div class="form-group mr-2">
                        <label for="staff_id" class="mr-2 small mb-0">Nhân viên</label>
                        <select name="staff_id" id="staff_id" class="form-control form-control-sm">
                            <option value="">Tất cả</option>
                            @foreach ($staffs as $staff)
                                <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mr-2">
                        <label for="date" class="mr-2 small mb-0">Ngày</label>
                        <input type="date" name="date" id="date" value="{{ request('date', $date ?? now()->toDateString()) }}" class="form-control form-control-sm">
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-primary">Lọc</button>
                </form>
            @endisset
        </div>
        <div class="card-body">
            @php
                $defaultStartTime = '09:00';
                $defaultEndTime = '18:00';
                $lateGraceMinutes = 15;
                $earlyLeaveGraceMinutes = 15;
            @endphp

            <table class="table table-bordered table-sm align-middle">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th>Nhân viên</th>
                        <th>Ca làm (chuẩn)</th>
                        <th>Giờ vào</th>
                        <th>Giờ ra</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staffs as $staff)
                        @php
                            $workDateString = $date;
                            $workDate = \Carbon\Carbon::parse($workDateString);
                            $isToday = $workDate->isToday();
                            $attendance = $attendances->get($staff->id);

                            if ($attendance && $attendance->schedule && $attendance->schedule->start_time) {
                                $expectedStart = $attendance->schedule->start_time->copy()->setDate($workDate->year, $workDate->month, $workDate->day);
                            } else {
                                $expectedStart = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $workDateString . ' ' . $defaultStartTime);
                            }

                            if ($attendance && $attendance->schedule && $attendance->schedule->end_time) {
                                $expectedEnd = $attendance->schedule->end_time->copy()->setDate($workDate->year, $workDate->month, $workDate->day);
                            } else {
                                $expectedEnd = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $workDateString . ' ' . $defaultEndTime);
                            }

                            $statusLabel = null;
                            $isLate = false;
                            $isEarlyLeave = false;

                            if ($attendance && $attendance->check_in_time) {
                                $checkIn = $attendance->check_in_time;
                                if ($checkIn->gt($expectedStart->copy()->addMinutes($lateGraceMinutes))) {
                                    $isLate = true;
                                    $statusLabel = 'Đi trễ';
                                } else {
                                    $statusLabel = 'Đúng giờ';
                                }
                            } elseif ($attendance) {
                                $statusLabel = 'Chưa chấm vào';
                            } else {
                                $statusLabel = 'Chưa có chấm công';
                            }

                            if ($attendance && $attendance->check_out_time) {
                                $checkOut = $attendance->check_out_time;
                                if ($checkOut->lt($expectedEnd->copy()->subMinutes($earlyLeaveGraceMinutes))) {
                                    $isEarlyLeave = true;
                                    $statusLabel = $statusLabel ? $statusLabel . ' / Về sớm' : 'Về sớm';
                                }
                            }

                            $statusClass = 'text-muted';
                            if ($isLate || $isEarlyLeave) {
                                $statusClass = 'text-danger font-weight-bold';
                            } elseif ($attendance && $attendance->check_in_time && $attendance->check_out_time && ! $isLate && ! $isEarlyLeave) {
                                $statusClass = 'text-success';
                            }
                        @endphp
                        <tr>
                            <td>{{ $workDate->format('d/m/Y') }}</td>
                            <td>{{ $staff->name }}</td>
                            <td>
                                {{ $expectedStart->format('H:i') }} - {{ $expectedEnd->format('H:i') }}
                            </td>
                            <td>
                                @if ($attendance && $attendance->check_in_time)
                                    {{ $attendance->check_in_time->format('H:i') }}
                                @else
                                    <span class="text-muted">Chưa chấm</span>
                                @endif
                            </td>
                            <td>
                                @if ($attendance && $attendance->check_out_time)
                                    {{ $attendance->check_out_time->format('H:i') }}
                                @else
                                    <span class="text-muted">Chưa chấm</span>
                                @endif
                            </td>
                            <td>
                                <span class="small {{ $statusClass }}">{{ $statusLabel }}</span>
                                @if ($attendance && $attendance->status)
                                    <div class="small text-muted">({{ $attendance->status }})</div>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    @if ($isToday && (! $attendance || ! $attendance->check_in_time))
                                        <form method="POST" action="{{ route('admin.hr.attendances.check-in') }}" class="mb-1">
                                            @csrf
                                            <input type="hidden" name="staff_id" value="{{ $staff->id }}">
                                            <input type="hidden" name="work_date" value="{{ $workDateString }}">
                                            <button type="submit" class="btn btn-sm btn-success btn-block">Chấm vào</button>
                                        </form>
                                    @endif

                                    @if ($isToday && $attendance && $attendance->check_in_time && ! $attendance->check_out_time)
                                        <form method="POST" action="{{ route('admin.hr.attendances.check-out', $attendance) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger btn-block">Chấm ra</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Chưa có nhân viên để chấm công.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3 small text-muted">
                <strong>Giả định & ràng buộc chấm công:</strong>
                <ul class="mb-0 pl-3">
                    <li>Giờ làm chuẩn nếu không có lịch ca: {{ $defaultStartTime }} - {{ $defaultEndTime }}.</li>
                    <li>Đi trễ khi chấm công vào sau {{ $lateGraceMinutes }} phút so với giờ bắt đầu ca.</li>
                    <li>Về sớm khi chấm công ra sớm hơn {{ $earlyLeaveGraceMinutes }} phút so với giờ kết thúc ca.</li>
                    <li>Trạng thái trong ngoặc là trạng thái gốc lưu trong bảng chấm công.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
