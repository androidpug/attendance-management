<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceDetailRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in' => ['required'],
            'clock_out' => ['required'],
            'comment' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'comment.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = $this->clock_in;
            $clockOut = $this->clock_out;

            // 出勤・退勤時間チェックを最初に
            if ($clockIn && $clockOut) {
                $clockInTime = strtotime($clockIn);
                $clockOutTime = strtotime($clockOut);

                if ($clockInTime >= $clockOutTime) {
                    $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
                    return;
                }
            }

            // 休憩時間チェック
            if ($this->breaks) {
                foreach ($this->breaks as $break) {
                    $breakIn = $break['break_in'] ?? null;
                    $breakOut = $break['break_out'] ?? null;

                    if ($breakIn && $clockIn && strtotime($breakIn) < strtotime($clockIn)) {
                        $validator->errors()->add('breaks', '休憩時間が不適切な値です');
                        return;
                    }

                    if ($breakIn && $clockOut && strtotime($breakIn) > strtotime($clockOut)) {
                        $validator->errors()->add('breaks', '休憩時間が不適切な値です');
                        return;
                    }

                    if ($breakOut && $clockOut && strtotime($breakOut) > strtotime($clockOut)) {
                        $validator->errors()->add('breaks', '休憩時間もしくは退勤時間が不適切な値です');
                        return;
                    }
                }
            }
        });
    }
}