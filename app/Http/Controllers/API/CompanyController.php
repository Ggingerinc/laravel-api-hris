<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCompanyRequest;
use App\Models\Company;
use GuzzleHttp\Promise\Create;
use http\Env\Response;
use http\Exception\RuntimeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function all(Request $request)
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
            if ($request->hasFile("logo")) {
                $path = $request->file("logo")->store("public/logos");
            } else {
                $path = null;
            }

            $company = Company::create([
                "name" => $request->name,
                "photo" => $path,
            ]);

            if (!$company) {
                throw new RuntimeException("Company was not created", 500);
            }

            $user = Auth::user();
            $user->companies()->attach($company->id);

            $company->load("users");

            return ResponseFormatter::success($company, "Company created successfully");
        } catch (\RuntimeException $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        } catch (\Exception $e) {
            return ResponseFormatter::error("An unexpected error occurred", 500);
        }   }
}
