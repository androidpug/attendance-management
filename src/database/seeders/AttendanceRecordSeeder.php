<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user1 = User::where('email', 'user1@example.com')->first();
        $user2 = User::where('email', 'user2@example.com')->first();
        $user3 = User::where('email', 'user3@example.com')->first();

        $this->seedUser1Data($user1);
        $this->seedSimpleData($user2);
        $this->seedSimpleData($user3);
    }

    /**
     * user1の意図的データを作成する（過去6ヶ月のレポート予測値と一致させる）
     *
     * @param User $user
     * @return void
     */
    private function seedUser1Data(User $user)
    {
        $today = Carbon::today();

        // 過去5ヶ月：各月平日15日（通常勤務 9:00-18:00）
        for ($i = 5; $i >= 1; $i--) {
            $targetMonth = $today->copy()->subMonths($i);
            $weekdays = $this->getWeekdaysOfMonth($targetMonth, 15);

            foreach ($weekdays as $date) {
                $this->createRecord($user, $date, '09:00:00', '18:00:00', 0); // status 0 = 勤務外スタート
            }
        }

        // 当月：17日分のパターン
        $thisMonthWeekdays = $this->getWeekdaysOfMonth($today, 17);

        // 通常勤務 10日
        for ($i = 0; $i < 10; $i++) {
            $this->createRecord($user, $thisMonthWeekdays[$i], '09:00:00', '18:00:00', 3);
        }

        // 残業 3日（9:00-20:00）
        for ($i = 10; $i < 13; $i++) {
            $this->createRecord($user, $thisMonthWeekdays[$i], '09:00:00', '20:00:00', 3);
        }

        // 遅刻 2日（9:30-18:00）
        for ($i = 13; $i < 15; $i++) {
            $this->createRecord($user, $thisMonthWeekdays[$i], '09:30:00', '18:00:00', 3);
        }

        // 早退 1日（9:00-17:00）
        $this->createRecord($user, $thisMonthWeekdays[15], '09:00:00', '17:00:00', 3);

        // 長時間労働 1日（8:00-21:00）
        $this->createRecord($user, $thisMonthWeekdays[16], '08:00:00', '21:00:00', 3);
    }

    /**
     * user2, user3用のシンプルなダミーデータを作成する
     *
     * @param User $user
     * @return void
     */
    private function seedSimpleData(User $user)
    {
        $today = Carbon::today();
        $weekdays = $this->getWeekdaysOfMonth($today, 10);

        foreach ($weekdays as $date) {
            $this->createRecord($user, $date, '09:00:00', '18:00:00', 3);
        }
    }

    /**
     * 勤怠レコードと固定休憩（12:00-13:00）を作成する
     *
     * @param User $user
     * @param Carbon $date
     * @param string $clockIn
     * @param string $clockOut
     * @param int $status
     * @return void
     */
    private function createRecord(User $user, Carbon $date, string $clockIn, string $clockOut, int $status)
    {
        $record = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => $date->format('Y-m-d'),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'status' => $status,
            'comment' => null,
        ]);

        BreakTime::create([
            'attendance_record_id' => $record->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);
    }

    /**
     * 指定月の平日を指定件数分取得する
     *
     * @param Carbon $month
     * @param int $count
     * @return array
     */
    private function getWeekdaysOfMonth(Carbon $month, int $count)
    {
        $weekdays = [];
        $date = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        while ($date->lte($endOfMonth) && count($weekdays) < $count) {
            if ($date->isWeekday()) {
                $weekdays[] = $date->copy();
            }
            $date->addDay();
        }

        return $weekdays;
    }
}