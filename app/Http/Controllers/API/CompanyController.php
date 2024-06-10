<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use GuzzleHttp\Promise\Create;
use http\Env\Response;
use http\Exception\RuntimeException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input("id");
        $name = $request->input("name");
        $limit = $request->input("limit", 10);

        if ($id) {
            $company = Company::with(["users"])->find($id);

            if ($company) {
                return ResponseFormatter::success($company, "Company found");
            }

            return ResponseFormatter::error("Company not found", 404);
        }

        $companies = Company::with(["users"]);

        if ($name) {
            $companies->where("name", "like", "%" . $name . "%");
        }

        return ResponseFormatter::success($companies->paginate($limit), "Companies found");
    }

    public function store(CreateCompanyRequest $request)
    {
        try {
            $path = null;

            if ($request->hasFile("logo")) {
                $path = $request->file("logo")->store("public/logos");
            }

            $company = Company::create([
                "name" => $request->name,
                "photo" => $path,
            ]);

            if (!$company) {
                throw new RuntimeException("Company was not created");
            }

            $user = Auth::user();
            $user->companies()->attach($company->id);

            $company->load("users");

            return ResponseFormatter::success($company, "Company created successfully");
        } catch (\RuntimeException $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ResponseFormatter::error("An unexpected error occurred", 500);
        }
    }

    public function update(UpdateCompanyRequest $request, string $id): JsonResponse
    {
        try {
            $company = Company::find($id);

            if (!$company) {
                throw new RuntimeException("Company not found");
            }

            $updatedData = [
                "name" => $request->name
            ];

            if ($request->hasFile("logo")) {
                $updatedData["logo"] = $request->file("logo")->store("public/logos");;
            }

            $company->update($updatedData);

            return ResponseFormatter::success($company, "Company updated successfully");
        } catch (\RuntimeException $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
