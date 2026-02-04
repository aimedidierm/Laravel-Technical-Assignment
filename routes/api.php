<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::post('auth/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('auth/password/forgot', [AuthController::class, 'forgotPassword'])->name('auth.password.forgot');
Route::post('auth/password/reset', [AuthController::class, 'resetPassword'])->name('auth.password.reset');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

    Route::apiResource('employees', EmployeeController::class);

    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::get('attendance/export/excel/{date}', [AttendanceController::class, 'exportExcel'])->name('attendance.export.excel');
    Route::get('attendance/export/pdf/{date}', [AttendanceController::class, 'exportPdf'])->name('attendance.export.pdf');
});
