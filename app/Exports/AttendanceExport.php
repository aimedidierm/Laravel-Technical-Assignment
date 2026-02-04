<?php

namespace App\Exports;

use App\Models\Attendance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AttendanceExport implements FromCollection, WithHeadings
{
    public function __construct(private string $date) {}

    public function collection(): Collection
    {
        return Attendance::with('employee')
            ->whereDate('check_in', $this->date)
            ->get()
            ->map(fn(Attendance $attendance) => [
                $attendance->employee->name,
                $attendance->employee->email,
                $attendance->check_in?->format('Y-m-d H:i:s'),
                $attendance->check_out?->format('Y-m-d H:i:s'),
            ]);
    }

    public function headings(): array
    {
        return ['Employee', 'Email', 'Check In', 'Check Out'];
    }
}
