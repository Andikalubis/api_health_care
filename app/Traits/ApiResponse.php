<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a success JSON response.
     * Mengembalikan response JSON sukses.
     */
    protected function successResponse($data, string $message = 'Berhasil', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return an error JSON response.
     * Mengembalikan response JSON error.
     */
    protected function errorResponse(string $message, int $code = 500, $error = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($error !== null) {
            $response['error'] = $error;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a validation error JSON response.
     * Mengembalikan response JSON error validasi.
     */
    protected function validationErrorResponse($errors, string $message = 'Validasi gagal'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], 422);
    }
}
