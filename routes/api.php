<?php

use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::get("/company", [CompanyController::class, "all"]);
Route::post("/login", [UserController::class, "login"]);
Route::post("/register", [UserController::class, "register"]);

Route::middleware("auth:sanctum")->group(function () {
    Route::get("user", [UserController::class, "fetch"]);
    Route::post("/logout", [UserController::class, "logout"]);
});


