@php
    //ログイン中かつ is_admin かどうかで判定
    if (Auth::check() && Auth::user()->is_admin) {
        $headerMenus = [
            '勤怠一覧' => route('admin.attendance.index'),
            'スタッフ一覧' => route('admin.staff.index'),
            '申請一覧' => route('request.index'),
            'ログアウト' => route('admin.logout'),
        ];
    } elseif (Auth::check()) {
        // 一般ユーザー
        // ★ ここで「退勤後の画面かどうか」を判定
        // ★修正ポイント：$isAfterWork が true の時だけメニューを切り替える
        if (isset($isAfterWork) && $isAfterWork) {
            // 勤怠登録画面（退勤後など）
            $headerMenus = [
                '今月の出勤一覧' => route('attendance.index'),
                '申請一覧' => route('request.index'),
                'ログアウト' => route('logout'),
            ];
        } else {
            // 一般ユーザー（is_admin が false）の場合
            $headerMenus = [
                '勤怠' => route('attendance.create'),
                '勤怠一覧' => route('attendance.index'),
                '申請' => route('request.index'),
                'ログアウト' => route('logout'),
            ];
        }
    } else {
        $headerMenus = [];
    }
@endphp

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>coachtech 勤怠管理アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header-inner">
            <div class="header-left">
                <a href="/">
                    <img src="{{ asset('img/COACHTECH_header_logo.png') }}" alt="COACHTECH" class="header-logo-image" />
                </a>
            </div>

            <nav class="header-right">
                @foreach ($headerMenus as $text => $path)
                    @if ($text === 'ログアウト')
                        {{-- ログアウトだけはPOST送信用のフォームにする --}}
                        <form action="{{ $path }}" method="POST" class="logout-form">
                            @csrf
                            <button type="submit" class="logout-button">{{ $text }}</button>
                        </form>
                    @else
                        <a href="{{ $path }}">{{ $text }}</a>
                    @endif
                @endforeach
            </nav>
        </div>
    </header>

    <main>
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif
        @yield('content')
    </main>
</body>

</html>
