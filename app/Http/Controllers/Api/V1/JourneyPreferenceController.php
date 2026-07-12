<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\UserJourney\Actions\ManagePersonalLibrary;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateJourneyPreferencesRequest;
use App\Http\Resources\Api\V1\JourneyPreferenceResource;
use App\Models\UserFandomPreference;
use App\Models\UserSpoilerPreference;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class JourneyPreferenceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', UserFandomPreference::class);
        $preferences = UserFandomPreference::query()->where('user_id', $request->user()->id)->get();
        $spoilers = UserSpoilerPreference::query()->where('user_id', $request->user()->id)->get()->keyBy('universe_id');
        $data = JourneyPreferenceResource::collection($preferences)->resolve($request);
        foreach ($data as &$item) {
            $spoiler = $spoilers->get($item['universe_id']);
            $item['spoiler_tolerance'] = $spoiler?->tolerance->value ?? 'strict';
            $item['show_warnings'] = $spoiler === null ? true : $spoiler->show_warnings;
            $item['rewatch_spoiler_behavior'] = $spoiler === null ? 'historical' : $spoiler->rewatch_behavior;
        }

        return ApiResponse::success($request, $data);
    }

    public function update(UpdateJourneyPreferencesRequest $request, ManagePersonalLibrary $action): JsonResponse
    {
        Gate::authorize('create', UserFandomPreference::class);
        $validated = $request->validated();
        $universeId = (int) $validated['universe_id'];
        unset($validated['universe_id']);
        $spoiler = array_intersect_key($validated, array_flip(['tolerance', 'show_warnings', 'rewatch_behavior', 'expected_version']));
        $fandom = array_diff_key($validated, array_flip(['tolerance', 'show_warnings', 'rewatch_behavior']));
        $preference = $action->updatePreferences($request->user(), $universeId, $fandom);
        if (count($spoiler) > 1 || array_key_exists('tolerance', $spoiler) || array_key_exists('show_warnings', $spoiler) || array_key_exists('rewatch_behavior', $spoiler)) {
            $action->updateSpoilerPreferences($request->user(), $universeId, $spoiler);
        }

        return ApiResponse::success($request, (new JourneyPreferenceResource($preference))->resolve($request));
    }
}
