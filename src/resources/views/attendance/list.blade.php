@extends('layouts.app')

@section('content')
<div class="attendance-list-container">
    <div class="attendance-list-inner">
        <h1 class="page-title">勤怠一覧</h1>

        <div class="month-nav">
            <a href="/attendance/list?month={{ $prevMonth }}" class="month-nav__prev">← 前月</a>
            <span class="month-nav__current">
                <img src="{{ asset('images/icon-calendar.svg') }}" alt="カレンダー" class="calendar-icon">
                {{ $currentMonth->format('Y/m') }}
            </span>
            <a href="/attendance/list?month={{ $nextMonth }}" class="month-nav__next">翌月 →</a>
        </div>

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
                @foreach($days as $day)
                <tr>
                    <td>{{ $day['date']->format('m/d') }}({{ ['日','月','火','水','木','金','土'][$day['date']->dayOfWeek] }})</td>
                    <td>{{ $day['record'] ? substr($day['record']->clock_in, 0, 5) : '' }}</td>
                    <td>{{ $day['record'] && $day['record']->clock_out ? substr($day['record']->clock_out, 0, 5) : '' }}</td>
                    <td>{{ $day['total_break'] ?? '' }}</td>
                    <td>{{ $day['total_work'] ?? '' }}</td>
                    <td>
                        @if($day['record'])
                            <a href="/attendance/detail/{{ $day['record']->id }}" class="detail-link">詳細</a>
                        @else
                            <a href="/attendance/detail/{{ $day['date']->format('Y-m-d') }}" class="detail-link">詳細</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection