@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/show.css') }}">
@endsection

@section('content')
    <div class="attendance-detail">
        <h1 class="attendance-detail-title">勤怠詳細</h1>


        @if ($isEditMode && !$attendance->isPending())
            <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
                @csrf
                @method('PATCH')
        @endif

        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td class="date-cell">
                    <span class="date-year">{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
                    <span class="date-month-day">{{ \Carbon\Carbon::parse($attendance->date)->isoFormat('M月DD日') }}</span>
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>

                    @if ($isEditMode && !$attendance->isPending())
                        <input type="time" name="start_time" class="start-time"
                            value="{{ old('start_time', \Carbon\Carbon::parse($attendance->start_time)->format('H:i')) }}">
                        <span class="time-separator">～</span>
                        <input type="time" name="end_time" class="end-time"
                            value="{{ old('end_time', $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '') }}">


                        <div class="error-message">
                            @error('start_time')
                                {{ $message }}
                            @enderror
                            @error('end_time')
                                {{ $message }}
                            @enderror
                        </div>
                    @else

                        {{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '--:--' }}
                        <span class="time-separator">～</span>
                        {{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '--:--' }}
                    @endif
                </td>
            </tr>


            @foreach ($attendance->rests as $index => $rest)
                <tr>
                    <th>休憩{{ $index === 0 ? '' : $index + 1 }}</th>
                    <td>
                        @if ($isEditMode && !$attendance->isPending())
                            <input type="hidden" name="rests[{{ $index }}][id]" value="{{ $rest->id }}">
                            <input class="start-time" type="time" name="rests[{{ $index }}][start_time]"
                                value="{{ old('rests.' . $index . '.start_time', \Carbon\Carbon::parse($rest->start_time)->format('H:i')) }}">
                            <span class="time-separator">～</span>
                            <input class="end-time" type="time" name="rests[{{ $index }}][end_time]"
                                value="{{ old('rests.' . $index . '.end_time', $rest->end_time ? \Carbon\Carbon::parse($rest->end_time)->format('H:i') : '') }}">

                            @if ($errors->has("rests.$index.start_time") || $errors->has("rests.$index.end_time"))
                                <div class="error-message">
                                    {{ $errors->first("rests.$index.start_time") ?: $errors->first("rests.$index.end_time") }}
                                </div>
                            @endif
                        @else
                            {{ \Carbon\Carbon::parse($rest->start_time)->format('H:i') }}
                            <span class="time-separator">～</span>
                            {{ $rest->end_time ? \Carbon\Carbon::parse($rest->end_time)->format('H:i') : '--:--' }}
                        @endif
                    </td>
                </tr>
            @endforeach

            @if ($isEditMode && !$attendance->isPending())
                <tr>
                    <th>休憩（追加）</th>
                    <td>
                        <input class="start-time" type="time" name="new_rests[0][start_time]"
                            value="{{ old('new_rests.0.start_time') }}">
                        <span class="time-separator">～</span>
                        <input class="end-time" type="time" name="new_rests[0][end_time]"
                            value="{{ old('new_rests.0.end_time') }}">

                        @if ($errors->has('new_rests.0.start_time') || $errors->has('new_rests.0.end_time'))
                            <div class="error-message">
                                {{ $errors->first('new_rests.0.start_time') ?: $errors->first('new_rests.0.end_time') }}
                            </div>
                        @endif
                    </td>
                </tr>
            @endif

            <tr>
                <th>備考</th>
                <td>
                    @if ($isEditMode && !$attendance->isPending())
                        <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
                        @error('note')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    @else
                        {{ $attendance->note ?? 'なし' }}
                    @endif
                </td>
            </tr>
        </table>

        <div class="detail-action">
            @if ($attendance->isPending())

                <p class="status-alert-box">*承認待ちのため修正はできません。</p>
            @elseif ($isEditMode)

                <button type="submit" class="submit-button">修正</button>
            @else

                <a href="{{ route('admin.attendance.show', ['id' => $attendance->id, 'mode' => 'edit']) }}"
                    class="edit-link">修正</a>
            @endif
        </div>
    </div>
@endsection
