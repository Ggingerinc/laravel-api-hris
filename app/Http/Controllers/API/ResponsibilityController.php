<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateResponsibilityRequest;
use App\Http\Requests\UpdateResponsibilityRequest;
use App\Models\Responsibility;
use http\Exception\RuntimeException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResponsibilityController extends Controller
{
    public function fetch(Request $request): JsonResponse
    {
        $id = $request->input("id");
        $name = $request->input("name");
        $limit = $request->input("limit");

        $responsibilityQuery = Responsibility::query();

        // Get a single data
        if ($id) {
            $responsibility = $responsibilityQuery->find($id);

            if ($responsibility) {
                return ResponseFormatter::success($responsibility, "Responsibility found");
            }

            return ResponseFormatter::error("Responsibility not found", 404);
        }

        // Get multiple data
        $responsibilities = $responsibilityQuery->where("role_id", $request->role_id);

        if ($name) {
            $responsibilities->where("name", "LIKE", "%" . $name . "%");
        }

        return ResponseFormatter::success($responsibilities->paginate($limit), "Responsibilities found");
    }

    public function store(CreateResponsibilityRequest $request): JsonResponse
    {
        try {
            $responsibility = Responsibility::create([
                "name" => $request->name,
                "role_id" => $request->role_id,
            ]);

            if (!$responsibility) {
                throw new \Exception("Responsibility was not created");
            }

            return ResponseFormatter::success($responsibility, "Responsibility created successfully");
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }


    public function destroy(string $id)
    {
        try {
            $responsibility = Responsibility::find($id);

            if (!$responsibility) {
                throw new \Exception("Responsibility not found");
            }

            $responsibility->delete();

            return ResponseFormatter::success([], "Responsibility deleted successfully");
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}

