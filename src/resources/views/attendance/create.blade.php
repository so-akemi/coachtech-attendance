@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
    <div class="attendance-content">
        {{-- ステータスごとに背景色を変えたい場合などのクラス命名 --}}
        <div class="status-badge status-{{ str_replace('_', '-', $status) }}">
            {{-- 文言の出し分け --}}
            @if ($status === 'before_work')
                勤務外
            @elseif($status === 'working')
                出勤中
            @elseif($status === 'resting')
                休憩中
            @else
                退勤済
            @endif
        </div>

        <div class="attendance-timer">
            <p class="attendance-date">{{ now()->format('Y年m月d日(D)') }}</p>
            <p class="attendance-time">{{ now()->format('H:i') }}</p>
        </div>

        <div class="attendance-actions">
            @if ($isBeforeWork)
                <form action="{{ route('attendance.store') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-main">出勤</button>
                </form>
            @endif

            @if ($isWorking)
                <form action="{{ route('attendance.update') }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-black">退勤</button>
                </form>
                <form action="{{ route('attendance.rest.store') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-white">休憩入</button>
                </form>
            @endif

            @if ($isResting)
                <form action="{{ route('attendance.rest.update') }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-white">休憩戻</button>
                </form>
            @endif

            @if ($isAfterWork)
                <p class="message">お疲れ様でした。</p>
            @endif
        </div>
    </div>
@endsection
