<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\DatePrecision;
use App\Enums\EpisodeType;
use App\Models\Episode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEpisodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('episode')) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $episode = $this->episode();

        return [
            'season_id' => ['sometimes', 'nullable', 'integer', 'exists:seasons,id'],
            'episode_number' => ['nullable', 'integer', 'min:0', Rule::unique('episodes')->where('season_id', $episode->season_id)->ignore($episode)],
            'display_number' => ['nullable', 'string', 'max:255'],
            'absolute_number' => ['nullable', 'integer', 'min:0', Rule::unique('episodes')->where('work_id', $episode->work_id)->ignore($episode)],
            'production_code' => ['nullable', 'string', 'max:255', Rule::unique('episodes')->where('work_id', $episode->work_id)->ignore($episode)],
            'type' => ['sometimes', Rule::enum(EpisodeType::class)],
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('episodes')->where('work_id', $episode->work_id)->ignore($episode)],
            'summary' => ['nullable', 'string', 'max:10000'],
            'synopsis' => ['nullable', 'string', 'max:100000'],
            'runtime_minutes' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'original_release_date' => ['nullable', 'date'],
            'release_date_precision' => ['nullable', Rule::enum(DatePrecision::class)],
            'position' => ['sometimes', 'integer', 'min:0'],
            'metadata' => ['sometimes', 'array'],
        ];
    }

    private function episode(): Episode
    {
        $episode = $this->route('episode');

        return $episode instanceof Episode ? $episode : throw new \LogicException('Episode route binding is required.');
    }
}
