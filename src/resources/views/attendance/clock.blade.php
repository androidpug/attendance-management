@extends('layouts.app')

@section('content')
<div class="clock-container">
    <div class="clock-inner">
        <span class="clock-status">
            @if($status === 'off') 勤務外
            @elseif($status === 'working') 出勤中
            @elseif($status === 'break') 休憩中
            @elseif($status === 'done') 退勤済
            @endif
        </span>

        <p class="clock-date" id="clock-date"></p>
        <p class="clock-time" id="clock-time"></p>

        <div class="clock-buttons">
            @if($status === 'off')
                <form action="/attendance/clock-in" method="POST">
                    @csrf
                    <button type="submit" class="btn btn--primary">出勤</button>
                </form>

            @elseif($status === 'working')
                <form action="/attendance/clock-out" method="POST">
                    @csrf
                    <button type="submit" class="btn btn--primary">退勤</button>
                </form>
                <form action="/attendance/break-in" method="POST">
                    @csrf
                    <button type="submit" class="btn btn--secondary">休憩入</button>
                </form>

            @elseif($status === 'break')
                <form action="/attendance/break-out" method="POST">
                    @csrf
                    <button type="submit" class="btn btn--secondary">休憩戻</button>
                </form>

            @elseif($status === 'done')
                <p class="clock-message">お疲れ様でした。</p>
            @endif
        </div>
    </div>
</div>

<script>
function updateClock() {
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth() + 1;
    const day = now.getDate();
    const weekdays = ['日', '月', '火', '水', '木', '金', '土'];
    const weekday = weekdays[now.getDay()];
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');

    document.getElementById('clock-date').textContent = 
        `${year}年${month}月${day}日(${weekday})`;
    document.getElementById('clock-time').textContent = 
        `${hours}:${minutes}`;
}

updateClock();
setInterval(updateClock, 1000);
</script>
@endsection