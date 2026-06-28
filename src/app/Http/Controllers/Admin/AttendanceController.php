<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function list(Request $request)
    {
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));
        $currentDate = Carbon::parse($date);

        $users = User::where('admin_status', false)->get();

        $records = AttendanceRecord::whereDate('date', $currentDate)
            ->with(['user', 'breakTimes'])
            ->get()
            ->keyBy('user_id');

        $attendances = [];
        foreach ($users as $user) {
            $record = $records->get($user->id);
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

            $attendances[] = [
                'user' => $user,
                'record' => $record,
                'total_break' => $totalBreak,
                'total_work' => $totalWork,
            ];
        }

        $prevDate = $currentDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $currentDate->copy()->addDay()->format('Y-m-d');

        return view('admin.attendance.list', compact('attendances', 'currentDate', 'prevDate', 'nextDate'));
    }
}