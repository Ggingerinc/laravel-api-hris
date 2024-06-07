<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

/**
 * Format response.
 */
class ResponseFormatter
{
    /**
     * API Response
     *
     * @var array
     */
    protected static array $response = [
        "meta" => [
            "code" => 200,
            "status" => "success",
            "message" => null
        ],
        "result" => null
    ];

    /**
     * Give success response
     *
     * @param $data
     * @param $message
     * @return JsonResponse
     */
    public static function success($data = null, $message = null): JsonResponse
    {
        self::$response["meta"]["message"] = $message;
        self::$response["result"] = $data;

        return response()->json(self::$response, self::$response["meta"]["code"]);
    }

    /**
     * Give error response
     *
     * @param $data
     * @param $message
     * @param int $code
     * @return JsonResponse
     */
    public static function error($message = null, int $code = 400): JsonResponse
    {
        self::$response["meta"]["status"] = "error";
        self::$response["meta"]["code"] = $code;
        self::$response["meta"]["message"] = $message;

        return response()->json(self::$response, self::$response["meta"]["code"]);
    }
}
