<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\CanonStatus;
use App\Enums\DatePrecision;
use App\Enums\EpisodeOrder;
use App\Enums\SeriesFormat;
use App\Enums\SeriesStatus;
use App\Enums\WorkReleaseStatus;
use App\Enums\WorkType;
use App\Models\Work;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('work')) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $work = $this->work();

        return [
            'expected_version' => ['required', 'integer', 'min:0'],
            'franchise_id' => ['nullable', 'integer', 'exists:franchises,id'],
            'type' => ['sometimes', Rule::enum(WorkType::class)],
            'slug' => ['sometimes', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('works')->where('universe_id', $work->universe_id)->ignore($work)],
            'original_title' => ['sometimes', 'string', 'max:255'],
            'original_language' => ['sometimes', 'string', 'max:35', 'regex:/^[A-Za-z]{2,3}(?:[-_][A-Za-z0-9]{2,8})*$/'],
            'summary' => ['nullable', 'string', 'max:10000'],
            'runtime_minutes' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'release_status' => ['sometimes', Rule::enum(WorkReleaseStatus::class)],
            'canon_status' => ['sometimes', Rule::enum(CanonStatus::class)],
            'original_release_date' => ['nullable', 'date'],
            'release_date_precision' => ['nullable', Rule::enum(DatePrecision::class)],
            'metadata' => ['sometimes', 'array'],
            'series_details' => ['sometimes', 'array'],
            'series_details.format' => ['sometimes', Rule::enum(SeriesFormat::class)],
            'series_details.series_status' => ['sometimes', Rule::enum(SeriesStatus::class)],
            'series_details.premiere_date' => ['nullable', 'date'],
            'series_details.end_date' => ['nullable', 'date', 'after_or_equal:series_details.premiere_date'],
            'series_details.default_episode_duration' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'series_details.default_episode_order' => ['sometimes', Rule::enum(EpisodeOrder::class)],
            'series_details.metadata' => ['sometimes', 'array'],
        ];
    }

    private function work(): Work
    {
        $work = $this->route('work');

        return $work instanceof Work ? $work : throw new \LogicException('Work route binding is required.');
    }
}
