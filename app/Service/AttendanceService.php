<?php

namespace App\Service;

use App\Exceptions\NotFoundException;
use App\Mail\AttendanceRecorded;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class AttendanceService
{
    public function getAll(): Collection
    {
        return Attendance::with('employee')->orderByDesc('check_in')->get();
    }

    public function checkIn(Employee $employee): Attendance
    {
        if ($employee->attendances()->whereDate('check_in', today())->exists()) {
            abort(409, 'Employee has already checked in today.');
        }

        $attendance = $employee->attendances()->create(['check_in' => now()]);

        Mail::to($employee->email)->queue(
            new AttendanceRecorded($employee, 'Check-In')
        );

        return $attendance;
    }

    public function checkOut(Employee $employee): Attendance
    {
        $attendance = $employee->attendances()
            ->whereDate('check_in', today())
            ->whereNull('check_out')
            ->first();

        if (!$attendance) {
            throw new NotFoundException('No active check-in found for today.');
        }

        $attendance->update(['check_out' => now()]);

        Mail::to($employee->email)->queue(
            new AttendanceRecorded($employee, 'Check-Out')
        );

        return $attendance;
    }
}
