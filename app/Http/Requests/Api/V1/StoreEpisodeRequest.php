<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\DatePrecision;
use App\Enums\EpisodeType;
use App\Models\Episode;
use App\Models\Season;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEpisodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Episode::class) && $this->user()->can('update', $this->season()->work);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $season = $this->season();

        return [
            'episode_number' => ['nullable', 'integer', 'min:0', Rule::unique('episodes')->where('season_id', $season->getKey())],
            'display_number' => ['nullable', 'string', 'max:255'],
            'absolute_number' => ['nullable', 'integer', 'min:0', Rule::unique('episodes')->where('work_id', $season->work_id)],
            'production_code' => ['nullable', 'string', 'max:255', Rule::unique('episodes')->where('work_id', $season->work_id)],
            'type' => ['sometimes', Rule::enum(EpisodeType::class)],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('episodes')->where('work_id', $season->work_id)],
            'summary' => ['nullable', 'string', 'max:10000'],
            'synopsis' => ['nullable', 'string', 'max:100000'],
            'runtime_minutes' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'original_release_date' => ['nullable', 'date'],
            'release_date_precision' => ['nullable', Rule::enum(DatePrecision::class)],
            'position' => ['sometimes', 'integer', 'min:0'],
            'metadata' => ['sometimes', 'array'],
        ];
    }

    private function season(): Season
    {
        $season = $this->route('season');

        return $season instanceof Season ? $season : throw new \LogicException('Season route binding is required.');
    }
}
