@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/show.css') }}">
@endsection

@section('content')
    <div class="attendance-detail">
        <h1 class="attendance-detail-title">勤怠詳細</h1>

        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $correctRequest->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td class="date-cell">
                    <span class="date-year">{{ \Carbon\Carbon::parse($correctRequest->date)->format('Y年') }}</span>
                    <span class="date-month-day">{{ \Carbon\Carbon::parse($correctRequest->date)->isoFormat('M月D日') }}</span>
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <!-- 申請された修正後の時間を表示 -->
                    {{ \Carbon\Carbon::parse($correctRequest->start_time)->format('H:i') }}
                    <span class="time-separator">～</span>
                    {{ \Carbon\Carbon::parse($correctRequest->end_time)->format('H:i') }}
                </td>
            </tr>

            <!-- 休憩時間の表示 -->
            @foreach ($correctRequest->restCorrectRequests as $index => $restRequest)
                <tr>
                    <th>休憩{{ $index === 0 ? '' : $index + 1 }}</th>
                    <td>
                        {{ \Carbon\Carbon::parse($restRequest->start_time)->format('H:i') }}
                        <span class="time-separator">～</span>
                        {{ $restRequest->end_time ? \Carbon\Carbon::parse($restRequest->end_time)->format('H:i') : '--:--' }}
                    </td>
                </tr>
            @endforeach

            <tr>
                <th>備考</th>
                <td>
                    <!-- 申請された備考を表示 -->
                    {{ $correctRequest->reason }}
                </td>
            </tr>
        </table>

        <div class="detail-action">
            @if ($correctRequest->status == 0)
                <!-- 承認待ち(0)の場合：承認ボタンを表示 (POST送信) -->
                <form action="{{ route('admin.request.approve.post', $correctRequest->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="approve-submit-button">承認</button>
                </form>
            @else
                <!-- 承認済み(1)の場合：グレーのボタンを表示 (無効化) -->
                <button type="button" class="approved-button" disabled>承認済み</button>
            @endif
        </div>
    </div>
@endsection
