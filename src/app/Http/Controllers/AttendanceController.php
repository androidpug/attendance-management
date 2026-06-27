<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        $status = 'off'; // 勤務外

        if ($attendance) {
            if ($attendance->status === 3) {
                $status = 'done'; // 退勤済
            } elseif ($attendance->status === 2) {
                $status = 'break'; // 休憩中
            } elseif ($attendance->status === 1) {
                $status = 'working'; // 出勤中
            }
        }

        return view('attendance.clock', compact('attendance', 'status'));
    }

    public function clockIn(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();

        $exists = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->exists();

        if (!$exists) {
            AttendanceRecord::create([
                'user_id' => $user->id,
                'date' => $today->format('Y-m-d'),
                'clock_in' => Carbon::now()->format('H:i:s'),
                'status' => 1,
            ]);
        }

        return redirect('/attendance');
    }

    public function breakIn(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($attendance && $attendance->status === 1) {
            BreakTime::create([
                'attendance_record_id' => $attendance->id,
                'break_in' => Carbon::now()->format('H:i:s'),
            ]);

            $attendance->update(['status' => 2]);
        }

        return redirect('/attendance');
    }

    public function breakOut(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($attendance && $attendance->status === 2) {
            $break = BreakTime::where('attendance_record_id', $attendance->id)
                ->whereNull('break_out')
                ->latest()
                ->first();

            if ($break) {
                $break->update(['break_out' => Carbon::now()->format('H:i:s')]);
            }

            $attendance->update(['status' => 1]);
        }

        return redirect('/attendance');
    }

    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($attendance && $attendance->status === 1) {
            $attendance->update([
                'clock_out' => Carbon::now()->format('H:i:s'),
                'status' => 3,
            ]);
        }

        return redirect('/attendance');
    }
}