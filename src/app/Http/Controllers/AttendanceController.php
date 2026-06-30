<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceDetailRequest;
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

    public function detail($id)
    {
        $user = Auth::user();

        $attendance = AttendanceRecord::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['breakTimes', 'attendanceCorrections'])
            ->firstOrFail();

        $isPending = $attendance->attendanceCorrections
            ->where('status', 0)
            ->count() > 0;

        return view('attendance.detail', compact('attendance', 'isPending'));
    }

    public function update(AttendanceDetailRequest $request, $id)
    {
        $user = Auth::user();

        $attendance = AttendanceRecord::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $isPending = $attendance->attendanceCorrections
            ->where('status', 0)
            ->count() > 0;

        if ($isPending) {
            return redirect("/attendance/detail/{$id}");
        }

        // 修正申請を作成
        $correction = $attendance->attendanceCorrections()->create([
            'user_id' => $user->id,
            'new_clock_in' => $request->clock_in,
            'new_clock_out' => $request->clock_out,
            'new_comment' => $request->comment,
            'status' => 0,
        ]);

        // 休憩の修正申請も保存
        if ($request->breaks) {
            foreach ($request->breaks as $breakId => $breakData) {
                if (!empty($breakData['break_in']) || !empty($breakData['break_out'])) {
                    $break = BreakTime::find($breakId);
                    if ($break) {
                        $break->update([
                            'break_in' => $breakData['break_in'] ?? $break->break_in,
                            'break_out' => $breakData['break_out'] ?? $break->break_out,
                        ]);
                    }
                }
            }
        }

        return redirect('/stamp_correction_request/list');
    }

    public function report()
    {
        $user = Auth::user();
        $now = Carbon::now();

        // 過去6ヶ月のデータを取得
        $months = [];
        $totalWorkMinutes = 0;
        $totalOvertimeMinutes = 0;
        $workDays = 0;

        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->startOfMonth()->subMonths($i);
            $records = AttendanceRecord::where('user_id', $user->id)
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->with('breakTimes')
                ->get();

            $monthWorkMinutes = 0;
            $monthOvertimeMinutes = 0;

            foreach ($records as $record) {
                if ($record->clock_in && $record->clock_out) {
                    $breakMinutes = 0;
                    foreach ($record->breakTimes as $break) {
                        if ($break->break_in && $break->break_out) {
                            $breakMinutes += Carbon::parse($break->break_out)
                                ->diffInMinutes(Carbon::parse($break->break_in));
                        }
                    }
                    $workMinutes = Carbon::parse($record->clock_out)
                        ->diffInMinutes(Carbon::parse($record->clock_in)) - $breakMinutes;
                    $monthWorkMinutes += $workMinutes;
                    $workDays++;

                    // 残業（8時間＝480分超）
                    if ($workMinutes > 480) {
                        $monthOvertimeMinutes += $workMinutes - 480;
                    }
                }
            }

            $totalWorkMinutes += $monthWorkMinutes;
            $totalOvertimeMinutes += $monthOvertimeMinutes;

            $months[] = [
                'month' => $month->format('Y-m'),
                'work' => sprintf('%dh %dm', intdiv($monthWorkMinutes, 60), $monthWorkMinutes % 60),
                'overtime' => sprintf('%dh %dm', intdiv($monthOvertimeMinutes, 60), $monthOvertimeMinutes % 60),
            ];
        }

        $avgWorkMinutes = $workDays > 0 ? intdiv($totalWorkMinutes, $workDays) : 0;

        // 今月の異常検知
        $thisMonth = AttendanceRecord::where('user_id', $user->id)
            ->whereYear('date', $now->year)
            ->whereMonth('date', $now->month)
            ->with('breakTimes')
            ->get();

        $lateCount = 0;
        $earlyLeaveCount = 0;
        $longWorkCount = 0;

        foreach ($thisMonth as $record) {
            if ($record->clock_in && Carbon::parse($record->clock_in)->format('H:i') > '09:00') {
                $lateCount++;
            }
            if ($record->clock_out && Carbon::parse($record->clock_out)->format('H:i') < '18:00') {
                $earlyLeaveCount++;
            }
            if ($record->clock_in && $record->clock_out) {
                $breakMinutes = 0;
                foreach ($record->breakTimes as $break) {
                    if ($break->break_in && $break->break_out) {
                        $breakMinutes += Carbon::parse($break->break_out)
                            ->diffInMinutes(Carbon::parse($break->break_in));
                    }
                }
                $workMinutes = Carbon::parse($record->clock_out)
                    ->diffInMinutes(Carbon::parse($record->clock_in)) - $breakMinutes;
                if ($workMinutes > 600) { // 10時間超
                    $longWorkCount++;
                }
            }
        }

        $summary = [
            'total_work' => sprintf('%dh %dm', intdiv($totalWorkMinutes, 60), $totalWorkMinutes % 60),
            'total_overtime' => sprintf('%dh %dm', intdiv($totalOvertimeMinutes, 60), $totalOvertimeMinutes % 60),
            'avg_work' => sprintf('%dh %dm', intdiv($avgWorkMinutes, 60), $avgWorkMinutes % 60),
        ];

        return view('attendance.report', compact('summary', 'months', 'lateCount', 'earlyLeaveCount', 'longWorkCount'));
    }
}