@extends('layouts.app')

@section('content')
<div class="attendance-list-container">
    <div class="attendance-list-inner">
        <h1 class="page-title">{{ $currentDate->format('Y年n月j日') }}の勤怠</h1>

        <div class="month-nav">
            <a href="/admin/attendance/list?date={{ $prevDate }}" class="month-nav__prev">← 前日</a>
            <span class="month-nav__current">
                <img src="{{ asset('images/icon-calendar.svg') }}" alt="カレンダー" class="calendar-icon">
                {{ $currentDate->format('Y/m/d') }}
            </span>
            <a href="/admin/attendance/list?date={{ $nextDate }}" class="month-nav__next">翌日 →</a>
        </div>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $attendance)
                <tr>
                    <td>{{ $attendance['user']->name }}</td>
                    <td>{{ $attendance['record'] ? substr($attendance['record']->clock_in, 0, 5) : '' }}</td>
                    <td>{{ $attendance['record'] && $attendance['record']->clock_out ? substr($attendance['record']->clock_out, 0, 5) : '' }}</td>
                    <td>{{ $attendance['total_break'] ?? '' }}</td>
                    <td>{{ $attendance['total_work'] ?? '' }}</td>
                    <td>
                        @if($attendance['record'])
                            <a href="/admin/attendance/{{ $attendance['record']->id }}" class="detail-link">詳細</a>
                        @else
                            <span class="detail-link detail-link--disabled">詳細</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection