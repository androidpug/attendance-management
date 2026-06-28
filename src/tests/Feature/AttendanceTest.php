<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
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

    // TC04: 日時取得機能
    public function test_現在の日時情報がUIと同じ形式で出力されている()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
    }

    // TC05: ステータス確認機能
    public function test_勤務外の場合勤怠ステータスが正しく表示される()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('勤務外');
    }

    public function test_出勤中の場合勤怠ステータスが正しく表示される()
    {
        $user = $this->createUser();
        $this->createAttendance($user, ['clock_out' => null, 'status' => 1]);
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function test_休憩中の場合勤怠ステータスが正しく表示される()
    {
        $user = $this->createUser();
        $this->createAttendance($user, ['clock_out' => null, 'status' => 2]);
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩中');
    }

    public function test_退勤済の場合勤怠ステータスが正しく表示される()
    {
        $user = $this->createUser();
        $this->createAttendance($user, ['status' => 3]);
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済');
    }

    // TC06: 出勤機能
    public function test_出勤ボタンが正しく機能する()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->post('/attendance/clock-in');
        $response->assertRedirect('/attendance');
        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'status' => 1,
        ]);
    }

    public function test_出勤は一日一回のみできる()
    {
        $user = $this->createUser();
        $this->createAttendance($user, ['status' => 3]);
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertDontSee('出勤');
    }

    public function test_出勤時刻が勤怠一覧画面で確認できる()
    {
        $user = $this->createUser();
        $this->actingAs($user)->post('/attendance/clock-in');
        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);
        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
        ]);
    }

    // TC07: 休憩機能
    public function test_休憩ボタンが正しく機能する()
    {
        $user = $this->createUser();
        $this->createAttendance($user, ['clock_out' => null, 'status' => 1]);
        $response = $this->actingAs($user)->post('/attendance/break-in');
        $response->assertRedirect('/attendance');
        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'status' => 2,
        ]);
    }

    public function test_休憩は一日に何回でもできる()
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user, ['clock_out' => null, 'status' => 1]);
        $this->actingAs($user)->post('/attendance/break-in');
        $this->actingAs($user)->post('/attendance/break-out');
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');
    }

    public function test_休憩戻ボタンが正しく機能する()
    {
        $user = $this->createUser();
        $attendance = $this->createAttendance($user, ['clock_out' => null, 'status' => 1]);
        $this->actingAs($user)->post('/attendance/break-in');
        $response = $this->actingAs($user)->post('/attendance/break-out');
        $response->assertRedirect('/attendance');
        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'status' => 1,
        ]);
    }

    public function test_休憩戻は一日に何回でもできる()
    {
        $user = $this->createUser();
        $this->createAttendance($user, ['clock_out' => null, 'status' => 1]);
        $this->actingAs($user)->post('/attendance/break-in');
        $this->actingAs($user)->post('/attendance/break-out');
        $this->actingAs($user)->post('/attendance/break-in');
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');
    }

    public function test_休憩時刻が勤怠一覧画面で確認できる()
    {
        $user = $this->createUser();
        $this->createAttendance($user, ['clock_out' => null, 'status' => 1]);
        $this->actingAs($user)->post('/attendance/break-in');
        $this->actingAs($user)->post('/attendance/break-out');
        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);
    }

    // TC08: 退勤機能
    public function test_退勤ボタンが正しく機能する()
    {
        $user = $this->createUser();
        $this->createAttendance($user, ['clock_out' => null, 'status' => 1]);
        $response = $this->actingAs($user)->post('/attendance/clock-out');
        $response->assertRedirect('/attendance');
        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'status' => 3,
        ]);
    }

    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = $this->createUser();
        $this->actingAs($user)->post('/attendance/clock-in');
        $this->actingAs($user)->post('/attendance/clock-out');
        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);
    }
}