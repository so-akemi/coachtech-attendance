@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/index.css') }}">
@endsection

@section('content')
    <div class="attendance-list">
        <!-- 設計書に合わせ「2023年6月1日の勤怠」のようなタイトルにするなら -->
        <h1 class="attendance-list-title">{{ $currentDate->format('Y年m月d日') }}の勤怠</h1>

        <div class="attendance-day-nav">
            <!-- route名は管理者用に設定したものに変更してください -->
            <a href="{{ route('admin.attendance.index', ['date' => $prevDate]) }}" class="day-nav-link">← 前日</a>
            <span class="day-nav-current">
                <span class="calendar-icon material-symbols-outlined">calendar_month</span>
                {{ $currentDate->format('Y/m/d') }}
            </span>
            <a href="{{ route('admin.attendance.index', ['date' => $nextDate]) }}" class="day-nav-link">翌日 →</a>
        </div>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>名前</th> <!-- ヘッダーは名前 -->
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
                        <!-- 1列目はユーザー名を表示 -->
                        <td>{{ $attendance->user->name }}</td>

                        <td>{{ $attendance->start_time ? Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '' }}</td>
                        <td>{{ $attendance->end_time ? Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '' }}</td>

                        <td>{{ $attendance->getTotalRestTime() }}</td>
                        <td>{{ $attendance->getWorkingTime() }}</td>

                        <td>
                            <!-- 管理者用の詳細（承認画面など）へのリンクにする -->
                            <a href="{{ route('admin.attendance.show', $attendance->id) }}" class="detail-link">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
