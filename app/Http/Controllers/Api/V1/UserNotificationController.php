<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\NotificationLifecycleStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserNotificationResource;
use App\Models\UserNotification;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UserNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', UserNotification::class);
        $query = UserNotification::query()->active()->where('user_id', $request->user()->id);
        if ($request->boolean('unread')) {
            $query->whereNull('read_at');
        }
        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }
        $paginator = $query->latest('created_at')->orderByDesc('id')->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));

        return ApiResponse::cursor($request, UserNotificationResource::collection($paginator->getCollection())->resolve($request), $paginator);
    }

    public function show(Request $request, UserNotification $notification): JsonResponse
    {
        $this->owned($request, $notification);

        return ApiResponse::success($request, (new UserNotificationResource($notification))->resolve($request));
    }

    public function read(Request $request, UserNotification $notification): JsonResponse
    {
        $this->owned($request, $notification);
        $notification->update(['read_at' => now()]);

        return ApiResponse::success($request, (new UserNotificationResource($notification))->resolve($request));
    }

    public function unread(Request $request, UserNotification $notification): JsonResponse
    {
        $this->owned($request, $notification);
        $notification->update(['read_at' => null]);

        return ApiResponse::success($request, (new UserNotificationResource($notification))->resolve($request));
    }

    public function archive(Request $request, UserNotification $notification): JsonResponse
    {
        $this->owned($request, $notification);
        $notification->update(['archived_at' => now(), 'status' => NotificationLifecycleStatus::Archived]);

        return ApiResponse::success($request, (new UserNotificationResource($notification))->resolve($request));
    }

    public function readAll(Request $request): JsonResponse
    {
        $updated = DB::transaction(fn (): int => UserNotification::query()->active()->where('user_id', $request->user()->id)->whereNull('read_at')->limit(500)->update(['read_at' => now()]));

        return ApiResponse::success($request, ['updated' => $updated]);
    }

    private function owned(Request $request, UserNotification $notification): void
    {
        abort_unless($notification->user_id === $request->user()->id, 404);
        Gate::authorize('view', $notification);
    }
}
