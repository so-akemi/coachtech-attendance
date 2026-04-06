@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')
    <div class="attendance-list">
        <h1 class="attendance-list-title">勤怠一覧</h1>

        <!-- 月選択ナビゲーション -->
        <div class="month-nav">
            <a href="{{ route('attendance.index', ['month' => $prevMonth]) }}" class="month-nav-link">← 前月</a>
            <span class="month-nav-current">
                <span class="calendar-icon material-symbols-outlined">calendar_month</span>
                {{ $currentMonth->format('Y/m') }}
            </span>
            <a href="{{ route('attendance.index', ['month' => $nextMonth]) }}" class="month-nav-link">翌月 →</a>
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
                @foreach ($attendances as $attendance)
                    <tr>
                        <td>{{ Carbon\Carbon::parse($attendance->date)->isoFormat('MM/DD(ddd)') }}</td>
                        <td>{{ $attendance->start_time ? Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '' }}
                        </td>
                        <td>{{ $attendance->end_time ? Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '' }}
                        </td>

                        <!-- 休憩合計を表示 -->
                        <td>{{ $attendance->getTotalRestTime() }}</td>

                        <!-- 勤務合計を表示 -->
                        <td>{{ $attendance->getWorkingTime() }}</td>

                        <td>
                            <a href="{{ route('attendance.show', $attendance->id) }}" class="detail-btn">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
