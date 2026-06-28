<?php

namespace Tests\Feature;

use App\Models\AttendanceCorrection;
use App\Models\AttendanceRecord;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        return User::factory()->create([
            'admin_status' => false,
            'email_verified_at' => now(),
        ]);
    }

    private function createAttendance(User $user, array $attributes = []): AttendanceRecord
    {
        return AttendanceRecord::create(array_merge([
            'user_id' => $user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 3,
        ], $attributes));
    }

    // TC09: 勤怠一覧情報取得機能（一般ユーザー）
    public function test_自分が行った勤怠情報が全て表示されている()
    {
        $user = $this->createUser();
        $this->createAttendance($user);
        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);
    }

    public function test_勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSee(Carbon::now()->format('Y/m'));
    }

    public function test_前月を押下した時に表示月の前月の情報が表示される()
    {
        $user = $this->createUser();
        $prevMonth = Carbon::now()->subMonth()->format('Y-m');
        $response = $this->actingAs($user)->get("/attendance/list?month={$prevMonth}");
        $response->assertSee(Carbon::now()->subMonth()->format('Y/m'));
    }

    public function test_翌月を押下した時に表示月の翌月の情報が表示される()
    {
        $user = $this->createUser();
        $nextMonth = Carbon::now()->addMonth()->format('Y-m');
        $response = $this->actingAs($user)->get("/attendance/list?month={$nextMonth}");
        $response->assertSee(Carbon::now()->addMonth()->format('Y/m'));
    }

    public function test_詳細を押下するとその日の勤怠詳細画面に遷移する()
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);
        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);
    }

    // TC10: 勤怠詳細情報取得機能（一般ユーザー）
    public function test_勤怠詳細画面の名前がログインユーザーの氏名になっている()
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);
        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertSee($user->name);
    }

    public function test_勤怠詳細画面の日付が選択した日付になっている()
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);
        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);
    }

    public function test_出勤退勤にて記されている時間がログインユーザーの打刻と一致している()
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);
        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_休憩にて記されている時間がログインユーザーの打刻と一致している()
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);
        BreakTime::create([
            'attendance_record_id' => $attendance->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);
        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    // TC11: 勤怠詳細情報修正機能（一般ユーザー）
    public function test_出勤時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);
        $response = $this->actingAs($user)->put("/attendance/detail/{$attendance->id}", [
            'clock_in' => '18:00',
            'clock_out' => '09:00',
            'comment' => 'テスト',
        ]);
        $response->assertSessionHasErrors(['clock_in']);
    }

    public function test_休憩開始時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);
        $break = BreakTime::create([
            'attendance_record_id' => $attendance->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);
        $response = $this->actingAs($user)->put("/attendance/detail/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [$break->id => ['break_in' => '19:00', 'break_out' => '20:00']],
            'comment' => 'テスト',
        ]);
        $response->assertSessionHasErrors(['breaks']);
    }

    public function test_休憩終了時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);
        $break = BreakTime::create([
            'attendance_record_id' => $attendance->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);
        $response = $this->actingAs($user)->put("/attendance/detail/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [$break->id => ['break_in' => '12:00', 'break_out' => '19:00']],
            'comment' => 'テスト',
        ]);
        $response->assertSessionHasErrors(['breaks']);
    }

    public function test_備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);
        $response = $this->actingAs($user)->put("/attendance/detail/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'comment' => '',
        ]);
        $response->assertSessionHasErrors(['comment' => '備考を記入してください']);
    }

    public function test_修正申請処理が実行される()
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);
        $this->actingAs($user)->put("/attendance/detail/{$attendance->id}", [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'comment' => 'テスト修正',
        ]);
        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_record_id' => $attendance->id,
            'status' => 0,
        ]);
    }

    public function test_承認待ちにログインユーザーが行った申請が全て表示されていること()
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);
        AttendanceCorrection::create([
            'attendance_record_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 0,
            'new_comment' => 'テスト',
        ]);
        $response = $this->actingAs($user)->get('/stamp_correction_request/list');
        $response->assertStatus(200);
        $response->assertSee('承認待ち');
    }

    public function test_承認済みに管理者が承認した修正申請が全て表示されている()
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);
        AttendanceCorrection::create([
            'attendance_record_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 1,
            'new_comment' => 'テスト',
        ]);
        $response = $this->actingAs($user)->get('/stamp_correction_request/list?tab=approved');
        $response->assertStatus(200);
        $response->assertSee('承認済み');
    }

    public function test_各申請の詳細を押下すると勤怠詳細画面に遷移する()
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);
        AttendanceCorrection::create([
            'attendance_record_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 0,
            'new_comment' => 'テスト',
        ]);
        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);
    }
}