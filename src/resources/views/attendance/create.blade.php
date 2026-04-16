@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance/create.css') }}">
@endsection

@section('content')
    <div class="attendance-content">
        <!-- ステータスごとに背景色を変えたい場合などのクラス命名 -->
        <div class="status-badge status-{{ str_replace('_', '-', $status) }}">
            <!-- 文言の出し分け -->
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
            <!--<p class="attendance-date">{{ now()->format('Y年m月d日(D)') }}</p>
                <p class="attendance-time">{{ now()->format('H:i') }}</p> -->
            <div id="date" class="attendance-date"></div>
            <div id="time" class="attendance-time"></div>

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

    <script>
        function updateClock() {
            const now = new Date();

            // --- 上段：日付の処理 ---
            const year = now.getFullYear();
            const month = now.getMonth() + 1;
            const date = now.getDate();
            const dayList = ["日", "月", "火", "水", "木", "金", "土"];
            const day = dayList[now.getDay()]; // 曜日を日本語に変換

            const dateString = `${year}年${month}月${date}日（${day}）`;

            // --- 下段：時刻の処理 ---
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');

            const timeString = `${hours}:${minutes}`;

            // HTMLに反映
            document.getElementById('date').textContent = dateString;
            document.getElementById('time').textContent = timeString;
        }

        // 1秒（1000ミリ秒）ごとに実行
        setInterval(updateClock, 1000);

        // ページ読み込み時にも即座に表示
        updateClock();
    </script>
@endsection
