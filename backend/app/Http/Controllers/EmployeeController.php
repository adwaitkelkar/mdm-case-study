<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class EmployeeController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $employees = Employee::query()
            ->withCount('assets')
            ->orderBy('name')
            ->get();

        return EmployeeResource::collection($employees);
    }

    /**
     * Deleting an employee only removes it from the local database - it
     * does not touch Jamf, and re-running sync recreates it if Jamf still
     * reports it. Since every asset in this system exists because it is
     * assigned to an employee, deleting the employee cascades to delete
     * their assets too (see migration + Readme "Assumptions").
     */
    public function destroy(Employee $employee): Response
    {
        $employee->delete();

        return response()->noContent();
    }
}
