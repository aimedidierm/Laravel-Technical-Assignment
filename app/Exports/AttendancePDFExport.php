<?php

namespace App\Exports;

use App\Models\Attendance;
use Barryvdh\LaravelSnappy\Facade\SnappyPdf;
use Symfony\Component\HttpFoundation\Response;

class AttendancePDFExport
{
    public function exportDailyPDF(string $date): Response
    {
        $attendances = Attendance::with('employee')
            ->whereDate('check_in', $date)
            ->get();

        return SnappyPdf::loadView('exports.attendance_pdf', compact('attendances', 'date'))
            ->download("attendance_report_{$date}.pdf");
    }
}
