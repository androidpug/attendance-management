<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech 勤怠管理</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="header">
        <a href="/" class="header__logo">
            <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
        </a>
        @auth
            <nav class="header__nav">
                @if(auth()->user()->admin_status)
                    <a href="/admin/attendance/list" class="header__nav-item">勤怠一覧</a>
                    <a href="/admin/staff/list" class="header__nav-item">スタッフ一覧</a>
                    <a href="/stamp_correction_request/list" class="header__nav-item">申請</a>
                @else
                    <a href="/attendance" class="header__nav-item">勤怠</a>
                    <a href="/attendance/list" class="header__nav-item">勤怠一覧</a>
                    <a href="/stamp_correction_request/list" class="header__nav-item">申請</a>
                    <a href="/attendance/report" class="header__nav-item">レポート</a>
                @endif
                <form action="/logout" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="header__nav-item header__logout">ログアウト</button>
                </form>
            </nav>
        @endauth
    </header>
    <main class="main">
        @yield('content')
    </main>
</body>
</html>