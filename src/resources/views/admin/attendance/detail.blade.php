@extends('layouts.app')

@section('content')
<div class="attendance-list-container">
    <div class="attendance-list-inner">
        <h1 class="page-title">勤怠詳細</h1>

        <form action="/admin/attendance/{{ $attendance->id }}" method="POST">
            @csrf
            @method('PUT')

            @if($errors->any())
                <div class="error-box">
                    @foreach($errors->all() as $error)
                        <p class="error-message">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="detail-card">
                <div class="detail-row">
                    <div class="detail-label">名前</div>
                    <div class="detail-value">{{ $attendance->user->name }}</div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">日付</div>
                    <div class="detail-value">
                        {{ Carbon\Carbon::parse($attendance->date)->format('Y年') }}
                        &nbsp;&nbsp;&nbsp;
                        {{ Carbon\Carbon::parse($attendance->date)->format('n月j日') }}
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">出勤・退勤</div>
                    <div class="detail-value detail-time">
                        @if($isPending)
                            <span>{{ substr($attendance->clock_in, 0, 5) }}</span>
                            <span class="detail-tilde">〜</span>
                            <span>{{ $attendance->clock_out ? substr($attendance->clock_out, 0, 5) : '' }}</span>
                        @else
                            <input type="text" name="clock_in" class="detail-input" value="{{ old('clock_in', substr($attendance->clock_in, 0, 5)) }}">
                            <span class="detail-tilde">〜</span>
                            <input type="text" name="clock_out" class="detail-input" value="{{ old('clock_out', $attendance->clock_out ? substr($attendance->clock_out, 0, 5) : '') }}">
                        @endif
                    </div>
                </div>

                @foreach($attendance->breakTimes as $index => $break)
                <div class="detail-row">
                    <div class="detail-label">休憩{{ $index > 0 ? $index + 1 : '' }}</div>
                    <div class="detail-value detail-time">
                        @if($isPending)
                            <span>{{ substr($break->break_in, 0, 5) }}</span>
                            <span class="detail-tilde">〜</span>
                            <span>{{ $break->break_out ? substr($break->break_out, 0, 5) : '' }}</span>
                        @else
                            <input type="text" name="breaks[{{ $break->id }}][break_in]" class="detail-input" value="{{ old('breaks.'.$break->id.'.break_in', substr($break->break_in, 0, 5)) }}">
                            <span class="detail-tilde">〜</span>
                            <input type="text" name="breaks[{{ $break->id }}][break_out]" class="detail-input" value="{{ old('breaks.'.$break->id.'.break_out', $break->break_out ? substr($break->break_out, 0, 5) : '') }}">
                        @endif
                    </div>
                </div>
                @endforeach

                @if(!$isPending)
                <div class="detail-row">
                    <div class="detail-label">休憩{{ $attendance->breakTimes->count() > 0 ? $attendance->breakTimes->count() + 1 : '' }}</div>
                    <div class="detail-value detail-time">
                        <input type="text" name="new_break_in" class="detail-input">
                        <span class="detail-tilde">〜</span>
                        <input type="text" name="new_break_out" class="detail-input">
                    </div>
                </div>
                @endif

                <div class="detail-row">
                    <div class="detail-label">備考</div>
                    <div class="detail-value">
                        @if($isPending)
                            <span>{{ $attendance->comment }}</span>
                        @else
                            <textarea name="comment" class="detail-textarea">{{ $attendance->comment }}</textarea>
                        @endif
                    </div>
                </div>
            </div>

            @if($isPending)
                <p class="pending-message">＊承認待ちのため修正はできません。</p>
            @else
                <div class="detail-submit">
                    <button type="submit" class="btn btn--primary">修正</button>
                </div>
            @endif
        </form>
    </div>
</div>
@endsection