@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/staffs/attendance.css') }}">
@endsection

@section('content')
    <div class="attendance-content">
        <h1 class="staff-name">{{ $user->name }}さんの勤怠</h1>

        <!-- 月選択ナビゲーション -->
        <div class="attendance-month-nav">
            <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $prevMonth]) }}"
                class="month-nav-link">
                <img class="month-nav-img-left" src="{{ asset('img/arrow-left.png') }}" alt="前月">
                前月
            </a>
            <span class="month-nav-current">
                <span class="calendar-icon material-symbols-outlined">calendar_month</span>
                {{ $currentMonth->format('Y/m') }}
            </span>
            <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $nextMonth]) }}"
                class="month-nav-link">
                翌月
                <img class="month-nav-img-right" src="{{ asset('img/arrow-right.png') }}" alt="翌月">
            </a>
        </div>

        <!-- 勤怠テーブル -->
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendanceList as $item)
                    <tr>
                        <!-- 日付は $item['date'] から取得 -->
                        <td>{{ $item['date']->isoFormat('MM/DD(ddd)') }}</td>

                        <!-- 勤務データがある場合 -->
                        @if ($item['attendance'])
                            @php $attendance = $item['attendance']; @endphp
                            <td>{{ $attendance->start_time ? Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '' }}
                            </td>
                            <td>{{ $attendance->end_time ? Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '' }}
                            </td>

                            <td>{{ $attendance->getTotalRestTime() }}</td>

                            <td>{{ $attendance->getWorkingTime() }}</td>

                            <td>
                                <!-- 管理者用の詳細画面へ -->
                                <a href="{{ route('admin.attendance.show', $attendance->id) }}" class="detail-btn">詳細</a>
                            </td>
                        @else
                            <!-- データがない日はすべて空白行にする -->
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>
                                <!-- データがない日は「詳細」を押せないようにするか、ハイフンにする -->
                                <a href="#" class="detail-btn">詳細</a>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="csv-export-container">
            <a
                href="{{ route('admin.attendance.staff.export', ['id' => $user->id, 'month' => $currentMonth->format('Y-m')]) }}"class="csv-btn">
                CSV出力
            </a>
        </div>
    </div>
@endsection
