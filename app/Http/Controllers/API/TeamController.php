<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use http\Exception\RuntimeException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function fetch(Request $request): JsonResponse
    {
        $id = $request->input("id");
        $name = $request->input("name");
        $limit = $request->input("limit");

        $teamQuery = Team::query();

        // Get a single data
        if ($id) {
            $team = $teamQuery->find($id);

            if ($team) {
                return ResponseFormatter::success($team, "Team found");
            }

            return ResponseFormatter::error("Team not found", 404);
        }

        // Get multiple data
        $teams = $teamQuery->where("company_id", $request->company_id);

        if ($name) {
            $teams->where("name", "LIKE", "%" . $name . "%");
        }

        return ResponseFormatter::success($teams->paginate($limit), "Teams found");
    }

    public function store(CreateTeamRequest $request): JsonResponse
    {
        try {
            if ($request->hasFile("icon")) {
                $path = $request->file("icon")->store("public/icons");
            }

            $team = Team::create([
                "name" => $request->name,
                "icon" => $path ?? null,
                "company_id" => $request->company_id,
            ]);

            if (!$team) {
                throw new \Exception("Team was not created");
            }

            return ResponseFormatter::success($team, "Team created successfully");
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateTeamRequest $request, string $id): JsonResponse
    {
        try {
            $team = Team::find($id);

            if (!$team) {
                throw new \Exception("Team not found");
            }

            if ($request->hasFile("icon")) {
                $path = $request->file("icon")->store("public/icons");
            }

            $team->update([
                "name" => $request->name,
                "icon" => $path ?? $team->icon,
                "company_id" => $request->company_id,
            ]);

            return ResponseFormatter::success($team, "Team updated successfully");
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $team = Team::find($id);

            if (!$team) {
                throw new \Exception("Team not found");
            }

            $team->delete();

            return ResponseFormatter::success([], "Team updated successfully");
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}

