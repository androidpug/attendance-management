@extends('layouts.app')

@section('content')
<div class="attendance-list-container">
    <div class="attendance-list-inner">
        <h1 class="page-title">申請一覧</h1>

        <div class="tab-nav">
            <a href="/stamp_correction_request/list?tab=pending" 
               class="tab-nav__item {{ $tab === 'pending' ? 'tab-nav__item--active' : '' }}">
                承認待ち
            </a>
            <a href="/stamp_correction_request/list?tab=approved" 
               class="tab-nav__item {{ $tab === 'approved' ? 'tab-nav__item--active' : '' }}">
                承認済み
            </a>
        </div>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @if($tab === 'pending')
                    @foreach($pending as $correction)
                    <tr>
                        <td>承認待ち</td>
                        <td>{{ $correction->attendanceRecord->user->name ?? '' }}</td>
                        <td>{{ $correction->attendanceRecord->date ?? '' }}</td>
                        <td>{{ $correction->new_comment }}</td>
                        <td>{{ $correction->created_at->format('Y/m/d') }}</td>
                        <td><a href="/attendance/detail/{{ $correction->attendance_record_id }}" class="detail-link">詳細</a></td>
                    </tr>
                    @endforeach
                @else
                    @foreach($approved as $correction)
                    <tr>
                        <td>承認済み</td>
                        <td>{{ $correction->attendanceRecord->user->name ?? '' }}</td>
                        <td>{{ $correction->attendanceRecord->date ?? '' }}</td>
                        <td>{{ $correction->new_comment }}</td>
                        <td>{{ $correction->created_at->format('Y/m/d') }}</td>
                        <td><a href="/attendance/detail/{{ $correction->attendance_record_id }}" class="detail-link">詳細</a></td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection