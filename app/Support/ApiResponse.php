<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;

class ApiResponse
{
    /**
     * Return a successful API v1 response.
     *
     * @param  array<string, mixed>  $meta
     * @param  array<string, string>  $headers
     */
    public static function success(
        Request $request,
        mixed $data,
        array $meta = [],
        int $status = 200,
        array $headers = [],
    ): JsonResponse {
        return response()->json([
            'data' => $data,
            'meta' => [...self::meta($request), ...$meta],
        ], $status, $headers);
    }

    /**
     * Return a failed API v1 response.
     *
     * @param  array<string, mixed>  $details
     * @param  array<string, string>  $headers
     */
    public static function error(
        Request $request,
        string $code,
        string $message,
        int $status,
        array $details = [],
        array $headers = [],
    ): JsonResponse {
        $error = ['code' => $code, 'message' => $message];

        if ($details !== []) {
            $error['details'] = $details;
        }

        return response()->json([
            'data' => null,
            'error' => $error,
            'meta' => self::meta($request),
        ], $status, $headers);
    }

    /**
     * Build metadata shared by every API v1 response.
     *
     * @return array{api_version: string, request_id: string|null}
     */
    public static function meta(Request $request): array
    {
        $requestId = $request->attributes->get('request_id');

        return [
            'api_version' => 'v1',
            'request_id' => is_string($requestId) ? $requestId : null,
        ];
    }

    /**
     * Return a cursor-paginated API v1 collection.
     *
     * @param  array<mixed>  $data
     * @param  CursorPaginator<int, mixed>  $paginator
     */
    public static function cursor(Request $request, array $data, CursorPaginator $paginator): JsonResponse
    {
        return self::success($request, $data, [
            'pagination' => [
                'per_page' => $paginator->perPage(),
                'next_cursor' => $paginator->nextCursor()?->encode(),
                'previous_cursor' => $paginator->previousCursor()?->encode(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ]);
    }
}
