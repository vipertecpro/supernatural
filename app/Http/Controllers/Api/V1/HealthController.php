<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    /**
     * Return a non-sensitive API availability response.
     */
    public function __invoke(Request $request): JsonResponse
    {
        return ApiResponse::success($request, ['status' => 'ok']);
    }
}
