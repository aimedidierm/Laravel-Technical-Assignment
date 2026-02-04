<?php

namespace Database\Factories;

use App\Enums\EmployeePosition;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'position' => $this->faker->randomElement(EmployeePosition::cases())->value,
            'phone' => $this->faker->phoneNumber(),
        ];
    }
}
