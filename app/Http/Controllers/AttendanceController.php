<?php

namespace App\Http\Controllers;

use App\Attributes\ApiEndpoint;
use App\Attributes\ApiResponse;
use App\Exports\AttendanceExport;
use App\Exports\AttendancePDFExport;
use App\Http\Requests\AttendanceRequest;
use App\Service\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $service,
        private readonly AttendancePDFExport $pdfExport
    ) {}

    #[ApiEndpoint(summary: 'List all attendance records', method: 'GET', path: '/api/attendance', tags: ['Attendance'])]
    #[ApiResponse(status: 200, description: 'List of attendance records')]
    public function index(): JsonResponse
    {
        return response()->json($this->service->getAll());
    }

    #[ApiEndpoint(summary: 'Record employee check-in', method: 'POST', path: '/api/attendance/check-in', tags: ['Attendance'])]
    #[ApiResponse(status: 201, description: 'Check-in recorded')]
    #[ApiResponse(status: 409, description: 'Already checked in today')]
    public function checkIn(AttendanceRequest $request): JsonResponse
    {
        $attendance = $this->service->checkIn($request->employee());

        return response()->json([
            'message' => 'Check-in recorded successfully.',
            'attendance' => $attendance,
        ], 201);
    }

    #[ApiEndpoint(summary: 'Record employee check-out', method: 'POST', path: '/api/attendance/check-out', tags: ['Attendance'])]
    #[ApiResponse(status: 200, description: 'Check-out recorded')]
    #[ApiResponse(status: 404, description: 'No active check-in found')]
    public function checkOut(AttendanceRequest $request): JsonResponse
    {
        $attendance = $this->service->checkOut($request->employee());

        return response()->json([
            'message' => 'Check-out recorded successfully.',
            'attendance' => $attendance,
        ]);
    }

    #[ApiEndpoint(summary: 'Export attendance as Excel', method: 'GET', path: '/api/attendance/export/excel/{date}', tags: ['Attendance'])]
    #[ApiResponse(status: 200, description: 'Excel file download')]
    public function exportExcel(string $date): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new AttendanceExport($date), "attendance_report_{$date}.xlsx");
    }

    #[ApiEndpoint(summary: 'Export attendance as PDF', method: 'GET', path: '/api/attendance/export/pdf/{date}', tags: ['Attendance'])]
    #[ApiResponse(status: 200, description: 'PDF file download')]
    public function exportPdf(string $date): \Symfony\Component\HttpFoundation\Response
    {
        return $this->pdfExport->exportDailyPDF($date);
    }
}
