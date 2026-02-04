<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'check_in' => $this->faker->dateTimeBetween('-1 day', 'now'),
            'check_out' => $this->faker->dateTimeBetween('-1 day', 'now'),
        ];
    }

    public function checkedIn(): static
    {
        return $this->state(fn() => [
            'check_in' => now(),
            'check_out' => null,
        ]);
    }
}
