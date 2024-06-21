<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use http\Exception\RuntimeException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function fetch(Request $request): JsonResponse
    {
        $id = $request->input("id");
        $name = $request->input("name");
        $email = $request->input("email");
        $age = $request->input("age");
        $phone = $request->input("phone");
        $teamId = $request->input("team-id");
        $roleId = $request->input("role-id");
        $companyId = $request->input("company-id");
        $limit = $request->input("limit");

        $employeeQuery = Employee::query();

        // Get a single data
        if ($id) {
            $employee = $employeeQuery->with(["team", "role"])->find($id);

            if ($employee) {
                return ResponseFormatter::success($employee, "Employee found");
            }

            return ResponseFormatter::error("Employee not found", 404);
        }

        // Get multiple data
        $employees = $employeeQuery;

        if ($name) {
            $employees->where("name", "LIKE", "%" . $name . "%");
        }

        if ($email) {
            $employees->where("email", $email);
        }

        if ($age) {
            $employees->where("age", $age);
        }

        if ($phone) {
            $employees->where("phone","LIKE", "%" . $phone . "%");
        }

        if ($teamId) {
            $employees->where("team_id", $teamId);
        }

        if ($roleId) {
            $employees->where("role_id", $roleId);
        }

        if($companyId) {
            $employees->whereHas("team", function($query) use ($companyId) {
                $query->where("company_id", $companyId);
            });
        }


        return ResponseFormatter::success($employees->simplePaginate($limit), "Employees found");
    }

    public function store(CreateEmployeeRequest $request): JsonResponse
    {
        try {
            if ($request->hasFile("photo")) {
                $path = $request->file("photo")->store("public/photos");
            }

            $employee = Employee::create([
                "name" => $request->name,
                "email" => $request->email,
                "gender" => $request->gender,
                "age" => $request->age,
                "phone" => $request->phone,
                "photo" => $path ?? null,
                "team_id" => $request->team_id,
                "role_id" => $request->role_id,
            ]);

            if (!$employee) {
                throw new \Exception("Employee was not created");
            }

            return ResponseFormatter::success($employee, "Employee created successfully");
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateEmployeeRequest $request, string $id): JsonResponse
    {
        try {
            $employee = Employee::find($id);

            if (!$employee) {
                throw new \Exception("Employee not found");
            }

            if ($request->hasFile("photo")) {
                $path = $request->file("photo")->store("public/photos");
            }

            $employee->update([
                "name" => $request->name,
                "email" => $request->email,
                "gender" => $request->gender,
                "age" => $request->age,
                "phone" => $request->phone,
                "photo" => $path ?? $employee->photo ,
                "team_id" => $request->team_id,
                "role_id" => $request->role_id,
            ]);

            return ResponseFormatter::success($employee, "Employee updated successfully");
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $employee = Employee::find($id);

            if (!$employee) {
                throw new \Exception("Employee not found");
            }

            $employee->delete();

            return ResponseFormatter::success([], "Employee deleted successfully");
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}

