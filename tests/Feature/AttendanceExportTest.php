<?php

namespace Tests\Feature;

use App\Exports\AttendancePDFExport;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $date = '2026-01-15';

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        $employee = Employee::factory()->create();

        Attendance::factory()->create([
            'employee_id' => $employee->id,
            'check_in' => "{$this->date} 09:00:00",
            'check_out' => "{$this->date} 17:00:00",
        ]);
    }

    // ─── Excel Export ────────────────────────────────────────────

    public function test_export_attendance_as_excel(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->get(route('attendance.export.excel', ['date' => $this->date]));

        $response->assertStatus(200);
        $this->assertStringContainsString(
            "attendance_report_{$this->date}.xlsx",
            $response->headers->get('Content-Disposition', '')
        );
    }

    // ─── PDF Export ──────────────────────────────────────────────

    public function test_export_attendance_as_pdf(): void
    {
        $this->mock(AttendancePDFExport::class, function ($mock) {
            $mock->shouldReceive('exportDailyPDF')
                ->with($this->date)
                ->andReturn(
                    new \Symfony\Component\HttpFoundation\Response(
                        '%PDF-1.4 mock content',
                        200,
                        [
                            'Content-Type' => 'application/pdf',
                            'Content-Disposition' => "attachment; filename=\"attendance_report_{$this->date}.pdf\"",
                        ]
                    )
                );
        });

        $response = $this->actingAs($this->user, 'sanctum')
            ->get(route('attendance.export.pdf', ['date' => $this->date]));

        $response->assertStatus(200);
        $this->assertStringContainsString(
            "attendance_report_{$this->date}.pdf",
            $response->headers->get('Content-Disposition', '')
        );
    }
}
