<?php

use App\Http\Controllers\Api\V1\AttendanceRecordController;
use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // 認証不要（GET）
    Route::get('/attendance-records', [AttendanceRecordController::class, 'index']);
    Route::get('/attendance-records/{attendanceRecord}', [AttendanceRecordController::class, 'show']);

    // 認証必須（POST/PUT/DELETE）
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/attendance-records', [AttendanceRecordController::class, 'store']);
        Route::put('/attendance-records/{attendanceRecord}', [AttendanceRecordController::class, 'update']);
        Route::delete('/attendance-records/{attendanceRecord}', [AttendanceRecordController::class, 'destroy']);
    });

    // 認証
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});