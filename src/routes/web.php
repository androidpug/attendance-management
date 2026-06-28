<?php

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\RequestController as AdminRequestController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/attendance');
});

// メール認証
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');
    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');
});

// 一般ユーザー認証が必要なルート
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
    Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn']);
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut']);
    Route::get('/attendance/list', [AttendanceController::class, 'list']);
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail']);
    Route::put('/attendance/detail/{id}', [AttendanceController::class, 'update']);
    Route::get('/stamp_correction_request/list', [RequestController::class, 'list']);
});

// 管理者認証
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    Route::get('/attendance/list', [AdminAttendanceController::class, 'list']);
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'detail']);
    Route::put('/attendance/{id}', [AdminAttendanceController::class, 'update']);
    Route::get('/staff/list', [AdminStaffController::class, 'list']);
    Route::get('/attendance/staff/{id}', [AdminStaffController::class, 'attendance']);
    Route::get('/stamp_correction_request/approve/{id}', [AdminRequestController::class, 'detail']);
    Route::post('/stamp_correction_request/approve/{id}', [AdminRequestController::class, 'approve']);
});