<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * 成功回應
     *
     * @param mixed $data 回傳資料
     * @param string $message 成功訊息
     * @param int $statusCode HTTP 狀態碼
     * @return JsonResponse
     */
    protected function successResponse(mixed $data = null, string $message = '操作成功', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * 錯誤回應
     *
     * @param string $message 錯誤訊息
     * @param int $statusCode HTTP 狀態碼
     * @return JsonResponse
     */
    protected function errorResponse(string $message, int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }
}
