<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AttendanceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->employee = Employee::factory()->create();
    }

    // ─── Index ───────────────────────────────────────────────────

    public function test_index_returns_attendance_records(): void
    {
        Attendance::factory(2)->create(['employee_id' => $this->employee->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson(route('attendance.index'))
            ->assertStatus(200)
            ->assertJsonCount(2);
    }

    // ─── Check-In ────────────────────────────────────────────────

    public function test_check_in_successfully(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson(route('attendance.check-in'), [
                'employee_id' => $this->employee->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Check-in recorded successfully.')
            ->assertJsonStructure(['attendance' => ['id', 'check_in']]);

        Mail::assertQueued(\App\Mail\AttendanceRecorded::class);
    }

    public function test_check_in_rejects_duplicate_on_same_day(): void
    {
        Attendance::factory()->create([
            'employee_id' => $this->employee->id,
            'check_in' => now(),
            'check_out' => null,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson(route('attendance.check-in'), [
                'employee_id' => $this->employee->id,
            ])->assertStatus(409);
    }

    public function test_check_in_rejects_invalid_employee(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson(route('attendance.check-in'), [
                'employee_id' => 9999,
            ])->assertStatus(422);
    }

    // ─── Check-Out ───────────────────────────────────────────────

    public function test_check_out_successfully(): void
    {
        Mail::fake();

        Attendance::factory()->create([
            'employee_id' => $this->employee->id,
            'check_in' => now(),
            'check_out' => null,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson(route('attendance.check-out'), [
                'employee_id' => $this->employee->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Check-out recorded successfully.')
            ->assertJsonStructure(['attendance' => ['check_out']]);

        Mail::assertQueued(\App\Mail\AttendanceRecorded::class);
    }

    public function test_check_out_without_active_check_in(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson(route('attendance.check-out'), [
                'employee_id' => $this->employee->id,
            ])->assertStatus(404)
            ->assertJsonPath('message', 'No active check-in found for today.');
    }
}
