<?php
namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    protected function success($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        $payload = [
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ];

        return response()->json($payload, $code);
    }

    protected function error(string $message = 'Error', int $code = 400, $errors = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if (!is_null($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $code);
    }

    protected function paginated(LengthAwarePaginator $paginator, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ], $code);
    }
}
