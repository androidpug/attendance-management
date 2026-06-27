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

    public function list(Request $request)
    {
        $user = Auth::user();
        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m', $month);

        $records = AttendanceRecord::where('user_id', $user->id)
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->get()
            ->keyBy(function ($record) {
                return Carbon::parse($record->date)->format('Y-m-d');
            });

        $days = [];
        $start = $currentMonth->copy()->startOfMonth();
        $end = $currentMonth->copy()->endOfMonth();

        for ($date = $start; $date->lte($end); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $record = $records->get($dateStr);

            $totalBreak = null;
            $totalWork = null;

            if ($record) {
                $breakMinutes = 0;
                foreach ($record->breakTimes as $break) {
                    if ($break->break_in && $break->break_out) {
                        $breakMinutes += Carbon::parse($break->break_out)
                            ->diffInMinutes(Carbon::parse($break->break_in));
                    }
                }

                if ($record->clock_in && $record->clock_out) {
                    $workMinutes = Carbon::parse($record->clock_out)
                        ->diffInMinutes(Carbon::parse($record->clock_in)) - $breakMinutes;
                    $totalWork = sprintf('%d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);
                }

                if ($breakMinutes > 0) {
                    $totalBreak = sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60);
                }
            }

            $days[] = [
                'date' => $date->copy(),
                'record' => $record,
                'total_break' => $totalBreak,
                'total_work' => $totalWork,
            ];
        }

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        return view('attendance.list', compact('days', 'currentMonth', 'prevMonth', 'nextMonth'));
    }
}