<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Search\Services\RelatedContentService;
use App\Domain\Search\Services\SearchQueryService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RelatedContentRequest;
use App\Http\Requests\Api\V1\SearchRequest;
use App\Http\Requests\Api\V1\SearchSuggestionRequest;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    public function index(SearchRequest $request, SearchQueryService $search): JsonResponse
    {
        $result = $search->search($request->string('q')->toString(), ['universe_id' => $request->input('filter.universe_id'), 'type' => $request->input('filter.type'), 'canon' => $request->input('filter.canon'), 'locale' => $request->input('locale'), 'page_size' => $request->input('page.size', 20), 'cursor' => $request->input('page.after')], $request->user());

        return ApiResponse::success($request, $result['items'], ['pagination' => ['per_page' => (int) $request->input('page.size', 20), 'next_cursor' => $result['next_cursor'], 'previous_cursor' => null, 'has_more' => $result['has_more']]]);
    }

    public function suggestions(SearchSuggestionRequest $request, SearchQueryService $search): JsonResponse
    {
        $items = $search->suggestions($request->string('q')->toString(), $request->safe()->only(['universe_id', 'locale', 'limit']), $request->user());

        return ApiResponse::success($request, $items);
    }

    public function related(RelatedContentRequest $request, string $type, int $id, RelatedContentService $related): JsonResponse
    {
        return ApiResponse::success($request, $related->related($type, $id, $request->integer('limit', 10), $request->user()));
    }
}
