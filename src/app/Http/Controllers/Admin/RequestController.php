<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceRecord;
use App\Models\BreakTime;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function list(Request $request)
    {
        $tab = $request->query('tab', 'pending');

        $pending = AttendanceCorrection::where('status', 0)
            ->with(['attendanceRecord.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $approved = AttendanceCorrection::where('status', 1)
            ->with(['attendanceRecord.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.request.list', compact('pending', 'approved', 'tab'));
    }

    public function detail($id)
    {
        $correction = AttendanceCorrection::with(['attendanceRecord.user', 'attendanceRecord.breakTimes'])
            ->findOrFail($id);

        return view('admin.request.detail', compact('correction'));
    }

    public function approve($id)
    {
        $correction = AttendanceCorrection::findOrFail($id);
        $attendance = $correction->attendanceRecord;

        // 勤怠情報を修正申請の内容で更新
        $attendance->update([
            'clock_in' => $correction->new_clock_in ?? $attendance->clock_in,
            'clock_out' => $correction->new_clock_out ?? $attendance->clock_out,
            'comment' => $correction->new_comment ?? $attendance->comment,
        ]);

        // 修正申請を承認済みに更新
        $correction->update([
            'status' => 1,
            'approved_by' => auth()->id(),
        ]);

        return redirect('/stamp_correction_request/list');
    }
}