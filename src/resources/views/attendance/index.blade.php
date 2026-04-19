@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')
    <div class="attendance-list">
        <h1 class="attendance-list-title">勤怠一覧</h1>

        <!-- 月選択ナビゲーション -->
        <div class="month-nav">
            <a href="{{ route('attendance.index', ['month' => $prevMonth]) }}" class="month-nav-link">
                <img class="month-nav-img-left" src="{{ asset('img/arrow-left.png') }}" alt="前月">
                前月
            </a>
            <span class="month-nav-current">
                <span class="calendar-icon material-symbols-outlined">calendar_month</span>
                {{ $currentMonth->format('Y/m') }}
            </span>
            <a href="{{ route('attendance.index', ['month' => $nextMonth]) }}" class="month-nav-link">
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
                        {{-- 日付：$item['date'] は Carbonインスタンス --}}
                        <td>{{ $item['date']->isoFormat('MM/DD(ddd)') }}</td>

                        {{-- データ（$item['attendance']）が存在する場合 --}}
                        @if ($item['attendance'])
                            @php $attendance = $item['attendance']; @endphp
                            <td>{{ $attendance->start_time ? Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '' }}
                            </td>
                            <td>{{ $attendance->end_time ? Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '' }}
                            </td>
                            <td>{{ $attendance->getTotalRestTime() }}</td>
                            <td>{{ $attendance->getWorkingTime() }}</td>
                            <td>
                                <a href="{{ route('attendance.show', $attendance->id) }}" class="detail-btn">詳細</a>
                            </td>
                        @else
                            {{-- データがない日は空白で表示 --}}
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>
                                <a href="#" class="detail-btn">詳細</a>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
