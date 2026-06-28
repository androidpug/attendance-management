@extends('layouts.app')

@section('content')
<div class="attendance-list-container">
    <div class="attendance-list-inner">
        <h1 class="page-title">勤怠詳細</h1>

        <div class="detail-card">
            <div class="detail-row">
                <div class="detail-label">名前</div>
                <div class="detail-value">{{ $correction->attendanceRecord->user->name }}</div>
            </div>

            <div class="detail-row">
                <div class="detail-label">日付</div>
                <div class="detail-value">
                    {{ Carbon\Carbon::parse($correction->attendanceRecord->date)->format('Y年') }}
                    &nbsp;&nbsp;&nbsp;
                    {{ Carbon\Carbon::parse($correction->attendanceRecord->date)->format('n月j日') }}
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">出勤・退勤</div>
                <div class="detail-value detail-time">
                    <span>{{ $correction->new_clock_in ? substr($correction->new_clock_in, 0, 5) : substr($correction->attendanceRecord->clock_in, 0, 5) }}</span>
                    <span class="detail-tilde">〜</span>
                    <span>{{ $correction->new_clock_out ? substr($correction->new_clock_out, 0, 5) : ($correction->attendanceRecord->clock_out ? substr($correction->attendanceRecord->clock_out, 0, 5) : '') }}</span>
                </div>
            </div>

            @foreach($correction->attendanceRecord->breakTimes as $index => $break)
            <div class="detail-row">
                <div class="detail-label">休憩{{ $index > 0 ? $index + 1 : '' }}</div>
                <div class="detail-value detail-time">
                    <span>{{ substr($break->break_in, 0, 5) }}</span>
                    <span class="detail-tilde">〜</span>
                    <span>{{ $break->break_out ? substr($break->break_out, 0, 5) : '' }}</span>
                </div>
            </div>
            @endforeach

            <div class="detail-row">
                <div class="detail-label">備考</div>
                <div class="detail-value">{{ $correction->new_comment ?? $correction->attendanceRecord->comment }}</div>
            </div>
        </div>

        @if($correction->status === 0)
            <form action="/admin/stamp_correction_request/approve/{{ $correction->id }}" method="POST">
                @csrf
                <div class="detail-submit">
                    <button type="submit" class="btn btn--primary">承認</button>
                </div>
            </form>
        @else
            <div class="detail-submit">
                <button class="btn btn--primary" disabled style="opacity: 0.5;">承認済み</button>
            </div>
        @endif
    </div>
</div>
@endsection