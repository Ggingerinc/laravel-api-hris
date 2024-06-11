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
use Psy\Util\Json;

class CompanyController extends Controller
{
    public function fetch(Request $request): JsonResponse
    {
        $id = $request->input("id");
        $name = $request->input("name");
        $limit = $request->input("limit", 10);

        $companyQuery = Company::with(["users"])->whereHas("users", function ($query) {
            $query->where("user_id", Auth::id());
        });

//        Get single data
        if ($id) {
//            only company that was assigned
            $company = $companyQuery->find($id);

            if ($company) {
                return ResponseFormatter::success($company, "Company found");
            }

            return ResponseFormatter::error("Company not found", 404);
        }

//        Get multiple data
        $companies = $companyQuery;

        if ($name) {
            $companies->where("name", "like", "%" . $name . "%");
        }

        return ResponseFormatter::success($companies->paginate($limit), "Companies found");
    }

    public function store(CreateCompanyRequest $request): JsonResponse
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
                throw new \Exception("Company was not created");
            }

            $user = Auth::user();
            $user->companies()->attach($company->id);

            $company->load("users");

            return ResponseFormatter::success($company, "Company created successfully");
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateCompanyRequest $request, string $id): JsonResponse
    {
        try {
            $company = Company::find($id);

            if (!$company) {
                throw new \Exception("Company not found");
            }

            $updatedData = [
                "name" => $request->name
            ];

            if ($request->hasFile("logo")) {
                $updatedData["logo"] = $request->file("logo")->store("public/logos");;
            }

            $company->update($updatedData);

            return ResponseFormatter::success($company, "Company updated successfully");
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
