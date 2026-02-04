<?php

namespace App\Http\Requests;

use App\Enums\EmployeePosition;
use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employee = $this->route('employee');
        $uniqueEmail = 'unique:employees,email' . ($employee ? ",{$employee->id}" : '');

        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', $uniqueEmail],
            'position' => ['required', 'in:' . implode(',', array_column(EmployeePosition::cases(), 'value'))],
            'phone' => 'nullable|string|max:20',
        ];
    }
}
