<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use http\Exception\RuntimeException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function fetch(Request $request): JsonResponse
    {
        $id = $request->input("id");
        $name = $request->input("name");
        $limit = $request->input("limit");

        $roleQuery = Role::query();

        // Get a single data
        if ($id) {
            $role = $roleQuery->find($id);

            if ($role) {
                return ResponseFormatter::success($role, "Role found");
            }

            return ResponseFormatter::error("Role not found", 404);
        }

        // Get multiple data
        $roles = $roleQuery->where("company_id", $request->company_id);

        if ($name) {
            $roles->where("name", "LIKE", "%" . $name . "%");
        }

        return ResponseFormatter::success($roles->paginate($limit), "Roles found");
    }

    public function store(CreateRoleRequest $request): JsonResponse
    {
        try {
            $role = Role::create([
                "name" => $request->name,
                "company_id" => $request->company_id,
            ]);

            if (!$role) {
                throw new \Exception("Role was not created");
            }

            return ResponseFormatter::success($role, "Role created successfully");
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateRoleRequest $request, string $id): JsonResponse
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                throw new \Exception("Role not found");
            }

            $role->update([
                "name" => $request->name,
                "company_id" => $request->company_id,
            ]);

            return ResponseFormatter::success($role, "Role updated successfully");
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                throw new \Exception("Role not found");
            }

            $role->delete();

            return ResponseFormatter::success([], "Role updated successfully");
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}

