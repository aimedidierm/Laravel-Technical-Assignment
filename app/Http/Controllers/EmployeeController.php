<?php

namespace App\Http\Controllers;

use App\Attributes\ApiEndpoint;
use App\Attributes\ApiResponse;
use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Service\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class EmployeeController extends Controller
{
    public function __construct(
        private readonly EmployeeService $service
    ) {}

    #[ApiEndpoint(summary: 'List all employees', method: 'GET', path: '/api/employees', tags: ['Employees'])]
    #[ApiResponse(status: 200, description: 'List of employees')]
    public function index(): JsonResponse
    {
        return response()->json($this->service->getAll());
    }

    #[ApiEndpoint(summary: 'Create an employee', method: 'POST', path: '/api/employees', tags: ['Employees'])]
    #[ApiResponse(status: 201, description: 'Employee created')]
    #[ApiResponse(status: 422, description: 'Validation error')]
    public function store(EmployeeRequest $request): JsonResponse
    {
        return response()->json(
            $this->service->create($request->validated()),
            201
        );
    }

    #[ApiEndpoint(summary: 'Get an employee', method: 'GET', path: '/api/employees/{employee}', tags: ['Employees'])]
    #[ApiResponse(status: 200, description: 'Employee details')]
    #[ApiResponse(status: 404, description: 'Employee not found')]
    public function show(Employee $employee): JsonResponse
    {
        return response()->json($employee);
    }

    #[ApiEndpoint(summary: 'Update an employee', method: 'PUT', path: '/api/employees/{employee}', tags: ['Employees'])]
    #[ApiResponse(status: 200, description: 'Employee updated')]
    #[ApiResponse(status: 422, description: 'Validation error')]
    public function update(EmployeeRequest $request, Employee $employee): JsonResponse
    {
        return response()->json(
            $this->service->update($employee, $request->validated())
        );
    }

    #[ApiEndpoint(summary: 'Delete an employee', method: 'DELETE', path: '/api/employees/{employee}', tags: ['Employees'])]
    #[ApiResponse(status: 204, description: 'Employee deleted')]
    public function destroy(Employee $employee): Response
    {
        $this->service->delete($employee);

        return response('', 204);
    }
}
