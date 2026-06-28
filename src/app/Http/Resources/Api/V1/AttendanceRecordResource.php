<?php

namespace App\Http\Resources\Api\V1;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
{
    public function toArray($request)
    {
        $breakMinutes = 0;
        if ($this->relationLoaded('breakTimes')) {
            foreach ($this->breakTimes as $break) {
                if ($break->break_in && $break->break_out) {
                    $breakMinutes += Carbon::parse($break->break_out)
                        ->diffInMinutes(Carbon::parse($break->break_in));
                }
            }
        }

        $totalTime = null;
        if ($this->clock_in && $this->clock_out) {
            $workMinutes = Carbon::parse($this->clock_out)
                ->diffInMinutes(Carbon::parse($this->clock_in)) - $breakMinutes;
            $totalTime = sprintf('%02d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);
        }

        $totalBreakTime = $breakMinutes > 0
            ? sprintf('%02d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60)
            : null;

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'date' => $this->date,
            'clock_in' => $this->clock_in,
            'clock_out' => $this->clock_out,
            'total_time' => $totalTime,
            'total_break_time' => $totalBreakTime,
            'comment' => $this->comment,
            'breaks' => AttendanceBreakResource::collection($this->whenLoaded('breakTimes')),
            'applications' => $this->whenLoaded('attendanceCorrections'),
        ];
    }
}