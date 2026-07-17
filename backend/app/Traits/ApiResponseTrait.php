<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Send a success response.
     */
    protected function successResponse(string $message = 'Success', array|object $data = [], int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => empty($data) ? new \stdClass() : $data,
        ], $code);
    }

    /**
     * Send an error response.
     */
    protected function errorResponse(string $message, int $code = 400, array|object $errors = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        } else {
            $response['errors'] = new \stdClass();
        }

        return response()->json($response, $code);
    }
}
