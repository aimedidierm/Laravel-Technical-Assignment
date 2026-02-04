<?php

namespace App\Service;

use App\Models\Employee;
use Illuminate\Support\Collection;

class EmployeeService
{
    public function getAll(): Collection
    {
        return Employee::all();
    }

    public function create(array $data): Employee
    {
        return Employee::create($data);
    }

    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);
        return $employee;
    }

    public function delete(Employee $employee): void
    {
        $employee->delete();
    }
}
