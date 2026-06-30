@extends('layouts.app')

@section('content')
<div class="attendance-list-container">
    <div class="attendance-list-inner">
        <h1 class="page-title">マイ勤怠レポート</h1>

        <p style="font-size: 14px; margin-bottom: 20px;">過去６ヶ月の勤怠データから集計しています。</p>

        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 15px;">基本サマリー</h2>
        <div style="display: flex; gap: 20px; margin-bottom: 30px;">
            <div class="report-card">
                <p class="report-card__label">総労働時間</p>
                <p class="report-card__value">{{ $summary['total_work'] }}</p>
            </div>
            <div class="report-card">
                <p class="report-card__label">総残業時間</p>
                <p class="report-card__value">{{ $summary['total_overtime'] }}</p>
            </div>
            <div class="report-card">
                <p class="report-card__label">平均労働時間 / 日</p>
                <p class="report-card__value">{{ $summary['avg_work'] }}</p>
            </div>
        </div>

        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 15px;">月次推移（過去６ヶ月）</h2>
        <table class="attendance-table" style="margin-bottom: 30px;">
            <thead>
                <tr>
                    <th>月</th>
                    <th>労働時間</th>
                    <th>残業時間</th>
                </tr>
            </thead>
            <tbody>
                @foreach($months as $month)
                <tr>
                    <td>{{ $month['month'] }}</td>
                    <td>{{ $month['work'] }}</td>
                    <td>{{ $month['overtime'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 5px;">今月の異常検知</h2>
        <p style="font-size: 14px; color: #666; margin-bottom: 15px;">基準: 始業 09:00 / 終業 18:00 / 長時間労働は１日 10 時間超</p>
        <div style="display: flex; gap: 20px;">
            <div class="report-card">
                <p class="report-card__label">遅刻回数</p>
                <p class="report-card__value">{{ $lateCount }}回</p>
            </div>
            <div class="report-card">
                <p class="report-card__label">早退回数</p>
                <p class="report-card__value">{{ $earlyLeaveCount }}回</p>
            </div>
            <div class="report-card">
                <p class="report-card__label">長時間労働日数</p>
                <p class="report-card__value">{{ $longWorkCount }}日</p>
            </div>
        </div>
    </div>
</div>
@endsection