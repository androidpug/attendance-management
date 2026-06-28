<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function list()
    {
        $users = User::where('admin_status', false)->get();
        return view('admin.staff.list', compact('users'));
    }

    public function attendance(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m', $month);

        $records = AttendanceRecord::where('user_id', $user->id)
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->with('breakTimes')
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

        return view('admin.staff.attendance', compact('user', 'days', 'currentMonth', 'prevMonth', 'nextMonth'));
    }

    public function csv(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m', $month);

        $records = AttendanceRecord::where('user_id', $user->id)
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->with('breakTimes')
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

            $totalBreak = '';
            $totalWork = '';

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
                'date' => $date->format('m/d') . '(' . ['日','月','火','水','木','金','土'][$date->dayOfWeek] . ')',
                'clock_in' => $record ? substr($record->clock_in, 0, 5) : '',
                'clock_out' => $record && $record->clock_out ? substr($record->clock_out, 0, 5) : '',
                'total_break' => $totalBreak,
                'total_work' => $totalWork,
            ];
        }

        $filename = $user->name . '_' . $currentMonth->format('Y-m') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($days) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel
            fputcsv($file, ['日付', '出勤', '退勤', '休憩', '合計']);
            foreach ($days as $day) {
                fputcsv($file, [
                    $day['date'],
                    $day['clock_in'],
                    $day['clock_out'],
                    $day['total_break'],
                    $day['total_work'],
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}