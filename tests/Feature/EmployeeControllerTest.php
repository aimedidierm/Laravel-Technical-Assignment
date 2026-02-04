<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ─── Index ───────────────────────────────────────────────────

    public function test_index_returns_all_employees(): void
    {
        Employee::factory(3)->create();

        $this->actingAs($this->user, 'sanctum')
            ->getJson(route('employees.index'))
            ->assertStatus(200)
            ->assertJsonCount(3);
    }

    // ─── Store ───────────────────────────────────────────────────

    public function test_store_creates_employee(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson(route('employees.store'), [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'position' => 'Developer',
                'phone' => '1234567890',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('name', 'Jane Smith')
            ->assertJsonPath('employee_id', 'EMP-0001');

        $this->assertDatabaseHas('employees', ['email' => 'jane@example.com']);
    }

    public function test_store_auto_increments_employee_id(): void
    {
        Employee::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson(route('employees.store'), [
                'name' => 'Bob',
                'email' => 'bob@example.com',
                'position' => 'Manager',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('employee_id', 'EMP-0002');
    }

    public function test_store_rejects_duplicate_email(): void
    {
        Employee::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($this->user, 'sanctum')
            ->postJson(route('employees.store'), [
                'name' => 'Dupe',
                'email' => 'taken@example.com',
                'position' => 'Designer',
            ])->assertStatus(422);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson(route('employees.store'), [])
            ->assertStatus(422);
    }

    public function test_store_rejects_invalid_position(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson(route('employees.store'), [
                'name' => 'Invalid',
                'email' => 'inv@example.com',
                'position' => 'InvalidPosition',
            ])->assertStatus(422);
    }

    // ─── Show ────────────────────────────────────────────────────

    public function test_show_returns_employee(): void
    {
        $employee = Employee::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->getJson(route('employees.show', $employee))
            ->assertStatus(200)
            ->assertJsonPath('id', $employee->id)
            ->assertJsonPath('name', $employee->name);
    }

    // ─── Update ──────────────────────────────────────────────────

    public function test_update_modifies_employee(): void
    {
        $employee = Employee::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson(route('employees.update', $employee), [
                'name' => 'Updated Name',
                'email' => $employee->email,
                'position' => 'Manager',
                'phone' => '9999999999',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('name', 'Updated Name')
            ->assertJsonPath('position', 'Manager');
    }

    public function test_update_allows_same_email_on_self(): void
    {
        $employee = Employee::factory()->create(['email' => 'own@example.com']);

        $this->actingAs($this->user, 'sanctum')
            ->putJson(route('employees.update', $employee), [
                'name' => 'Same Email',
                'email' => 'own@example.com',
                'position' => 'Engineer',
            ])->assertStatus(200);
    }

    // ─── Destroy ─────────────────────────────────────────────────

    public function test_destroy_removes_employee(): void
    {
        $employee = Employee::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson(route('employees.destroy', $employee))
            ->assertStatus(204);

        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }
}
