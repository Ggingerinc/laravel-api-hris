<?php

use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;


//    Company API
Route::prefix("company")->middleware("auth:sanctum")->name("company.")->group(function () {
    Route::get("", [CompanyController::class, "fetch"])->name("fetch");
    Route::post("", [CompanyController::class, "store"])->name("store");
    Route::post("update/{id}", [CompanyController::class, "update"])->name("update");
});

Route::resource("role", \App\Http\Controllers\RoleController::class);

// Auth API
Route::name("auth.")->group(function () {
    Route::post("login", [UserController::class, "login"])->name("login");
    Route::post("register", [UserController::class, "register"])->name("register");

    Route::middleware("auth:sanctum")->group(function () {
        Route::post("logout", [UserController::class, "logout"])->name("logout");
        Route::get("user", [UserController::class, "fetch"])->name("fetch");
    });
});

