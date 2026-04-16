@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/show.css') }}">
@endsection

@section('content')
    <div class="attendance-detail">
        <h1 class="attendance-detail-title">勤怠詳細</h1>

        <!-- 修正モードの時だけフォームを開始 -->
        @if ($isEditMode)
            <form action="{{ route('attendance.updateRequest', $attendance->id) }}" method="POST">
                @csrf
        @endif

        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    <div class="date-container">
                        <span class="date-year">
                            {{ Carbon\Carbon::parse($attendance->date)->format('Y年') }}
                        </span>
                        <span class="date-month-day">
                            {{ Carbon\Carbon::parse($attendance->date)->isoFormat('MM月DD日') }}
                        </span>
                    </div>

                    <!-- もし、サーバー側に日付データを送る必要がある場合は、hiddenで持たせておく -->
                    @if ($isEditMode)
                        <input type="hidden" name="date" value="{{ $attendance->date }}">
                    @endif
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    @if ($isEditMode)
                        <input type="time" name="start_time" class="start-time"
                            value="{{ old('start_time', \Carbon\Carbon::parse($attendance->start_time)->format('H:i')) }}">
                        ～
                        <input type="time" name="end_time" class="end-time"
                            value="{{ old('end_time', $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '') }}">
                        <div class="error-message">
                            @if ($errors->has('start_time'))
                                {{ $errors->first('start_time') }}
                            @elseif($errors->has('end_time'))
                                {{ $errors->first('end_time') }}
                            @endif
                        </div>
                    @else
                        {{ $attendance->start_time ? Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '--:--' }}
                        ～
                        {{ $attendance->end_time ? Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '--:--' }}
                    @endif
                </td>
            </tr>

            @foreach ($attendance->rests as $index => $rest)
                <tr>
                    <th>休憩{{ $index === 0 ? '' : $index + 1 }}</th>
                    <td>
                        @if ($isEditMode)
                            <input type="hidden" name="rests[{{ $index }}][id]" value="{{ $rest->id }}">
                            <input class="start-time" type="time" name="rests[{{ $index }}][start_time]"
                                value="{{ old('rests.' . $index . '.start_time', \Carbon\Carbon::parse($rest->start_time)->format('H:i')) }}">
                            ～
                            <input class="end-time" type="time" name="rests[{{ $index }}][end_time]"
                                value="{{ old('rests.' . $index . '.end_time', $rest->end_time ? \Carbon\Carbon::parse($rest->end_time)->format('H:i') : '') }}">
                            @php
                                $startKey = "rests.$index.start_time";
                                $endKey = "rests.$index.end_time";
                                // この休憩行のどちらかにエラーがあるか
                                $hasError = $errors->has($startKey) || $errors->has($endKey);
                                // 表示するメッセージ（最初に見つかった方を優先）
                                $Message = $errors->first($startKey) ?: $errors->first($endKey);
                            @endphp

                            @if ($hasError)
                                <div class="error-message">
                                    {{ $Message }}
                                </div>
                            @endif
                        @else
                            {{ Carbon\Carbon::parse($rest->start_time)->format('H:i') }}
                            ～
                            {{ $rest->end_time ? Carbon\Carbon::parse($rest->end_time)->format('H:i') : '--:--' }}
                        @endif
                    </td>
                </tr>
            @endforeach

            @if ($isEditMode)
                <tr>
                    <th>休憩{{ count($attendance->rests) + 1 }} (新規)</th>
                    <td>
                        <input class="start-time" type="time" name="new_rests[0][start_time]">
                        ～
                        <input class="end-time" type="time" name="new_rests[0][end_time]">
                    </td>
                </tr>
            @endif

            <tr>
                <th>備考</th>
                <td>
                    @if ($isEditMode)
                        <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
                        <div class="error-message">
                            @error('note')
                                {{ $message }}
                            @enderror
                        </div>
                    @else
                        {{ $attendance->note ?? 'なし' }}
                    @endif
                </td>
            </tr>
        </table>

        <!-- ボタンエリア -->
        <div class="detail-action">
            @if ($isEditMode)
                <button type="submit" class="submit-button">修正</button>
            @else
                @if (!$attendance->isPending())
                    <a href="{{ route('attendance.show', ['id' => $attendance->id, 'mode' => 'edit']) }}"
                        class="edit-link">修正</a>
                @endif
            @endif
        </div>

        @if ($isEditMode)
            </form>
        @endif

        @if ($attendance->isPending())
            <p class="pending-message">
                *承認待ちのため修正はできません。
            </p>
        @endif
    </div>
@endsection
