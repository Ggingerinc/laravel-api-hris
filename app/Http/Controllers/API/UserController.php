<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
//        Validate the request

        try {
//            Basic validation
//            $request->validate([
//                "email" => "required|email",
//                "password" => "required"
//            ]);
//
////        Find user by email
//            $credentials = \request(["email", "password"]);
//            if (!Auth::attempt($credentials)) {
//                return ResponseFormatter::error("Unauthorized", 401);
//            }
//
////            User::whereEmail()
//            $user = User::where("email", $request->email)->first();
//            if (!Hash::check($request->password, $user->password)) {
//                throw new \Exception("Invalid password");
//            }

//            Use form request and throttle
            $request->authenticate();

            $user = User::where("email", $request->email)->first();
            if (!Hash::check($request->password, $user->password)) {
                throw new \Exception("Invalid password");
            }

//        Generate token
            $tokenResult = $user->createToken("authToken")->plainTextToken;

//        Return response
            return ResponseFormatter::success([
                "access_token" => $tokenResult,
                "token_type" => "Bearer",
                "user" => $user
            ], "Login success");
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                "name" => ["required", "string", "max:255"],
                "email" => ["required", "string", "lowercase", "email", "max:255", "unique:".User::class],
                "password" => ["required", "string", Password::min(8)->mixedCase()->numbers()
                    ->letters()->symbols()]
            ]);

            $user = User::create([
                "name" => $request->name,
                "email" => $request->email,
                "password" => Hash::make($request->password)
            ]);

            event(new Registered($user));

            $tokenResult = $user->createToken("authToken")->plainTextToken;

            return ResponseFormatter::success([
                "access_token" => $tokenResult,
                "token_type" => "Bearer",
                "user" => $user
            ], "Register success", 201);
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        try {
//       Revoke Token
            $tokenId = $request->user()->currentAccessToken()['id'];
            $request->user()->tokens()->where("id", $tokenId)->delete();

//        Return response
            return ResponseFormatter::success([], "Logout success");
        } catch (\Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    public function fetch(Request $request)
    {
        $user = $request->user();

        return ResponseFormatter::success($user, "Fetch success");
    }
}
