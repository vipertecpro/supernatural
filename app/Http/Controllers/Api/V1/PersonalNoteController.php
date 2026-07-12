<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\UserJourney\Actions\ManagePersonalLibrary;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StorePersonalNoteRequest;
use App\Http\Resources\Api\V1\PersonalNoteResource;
use App\Models\PersonalNote;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PersonalNoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', PersonalNote::class);
        $paginator = PersonalNote::query()->where('user_id', $request->user()->id)->latest('updated_at')->orderByDesc('id')->cursorPaginate(min(max($request->integer('page.size', 20), 1), 50));

        return ApiResponse::cursor($request, PersonalNoteResource::collection($paginator->getCollection())->resolve($request), $paginator);
    }

    public function store(StorePersonalNoteRequest $request, ManagePersonalLibrary $action): JsonResponse
    {
        Gate::authorize('create', PersonalNote::class);
        $note = $action->createNote($request->user(), $request->validated());

        return ApiResponse::success($request, (new PersonalNoteResource($note))->resolve($request), status: 201);
    }

    public function show(Request $request, PersonalNote $note): JsonResponse
    {
        $this->owned($request, $note);
        Gate::authorize('update', $note);

        return ApiResponse::success($request, (new PersonalNoteResource($note))->resolve($request));
    }

    public function update(StorePersonalNoteRequest $request, PersonalNote $note, ManagePersonalLibrary $action): JsonResponse
    {
        $this->owned($request, $note);
        Gate::authorize('delete', $note);
        $note = $action->updateNote($request->user(), $note, $request->validated());

        return ApiResponse::success($request, (new PersonalNoteResource($note))->resolve($request));
    }

    public function destroy(Request $request, PersonalNote $note): JsonResponse
    {
        $this->owned($request, $note);
        $note->delete();

        return ApiResponse::success($request, null);
    }

    private function owned(Request $request, PersonalNote $note): void
    {
        abort_unless($note->user_id === $request->user()->id, 404);
        Gate::authorize('view', $note);
    }
}
