<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\CanonStatus;
use App\Enums\DatePrecision;
use App\Enums\EpisodeOrder;
use App\Enums\SeriesFormat;
use App\Enums\SeriesStatus;
use App\Enums\WorkReleaseStatus;
use App\Enums\WorkType;
use App\Models\Universe;
use App\Models\Work;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Work::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'franchise_id' => ['nullable', 'integer', 'exists:franchises,id'],
            'type' => ['required', Rule::enum(WorkType::class)],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('works')->where('universe_id', $this->universe()->getKey())],
            'original_title' => ['required', 'string', 'max:255'],
            'original_language' => ['required', 'string', 'max:35', 'regex:/^[A-Za-z]{2,3}(?:[-_][A-Za-z0-9]{2,8})*$/'],
            'summary' => ['nullable', 'string', 'max:10000'],
            'runtime_minutes' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'release_status' => ['sometimes', Rule::enum(WorkReleaseStatus::class)],
            'canon_status' => ['sometimes', Rule::enum(CanonStatus::class)],
            'original_release_date' => ['nullable', 'date'],
            'release_date_precision' => ['nullable', Rule::enum(DatePrecision::class)],
            'metadata' => ['sometimes', 'array'],
            'series_details' => ['array', Rule::requiredIf($this->input('type') === WorkType::Series->value), Rule::prohibitedIf($this->input('type') !== WorkType::Series->value)],
            'series_details.format' => ['required_with:series_details', Rule::enum(SeriesFormat::class)],
            'series_details.series_status' => ['sometimes', Rule::enum(SeriesStatus::class)],
            'series_details.premiere_date' => ['nullable', 'date'],
            'series_details.end_date' => ['nullable', 'date', 'after_or_equal:series_details.premiere_date'],
            'series_details.default_episode_duration' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'series_details.default_episode_order' => ['sometimes', Rule::enum(EpisodeOrder::class)],
            'series_details.metadata' => ['sometimes', 'array'],
        ];
    }

    private function universe(): Universe
    {
        $universe = $this->route('universe');

        return $universe instanceof Universe ? $universe : throw new \LogicException('Universe route binding is required.');
    }
}
