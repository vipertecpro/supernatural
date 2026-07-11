<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\DatePrecision;
use App\Enums\SeasonType;
use App\Models\Season;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSeasonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('season')) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $season = $this->season();

        return [
            'type' => ['sometimes', Rule::enum(SeasonType::class)],
            'number' => ['nullable', 'integer', 'min:0', Rule::unique('seasons')->where('work_id', $season->work_id)->ignore($season)],
            'display_number' => ['nullable', 'string', 'max:255'],
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('seasons')->where('work_id', $season->work_id)->ignore($season)],
            'summary' => ['nullable', 'string', 'max:10000'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'original_release_date' => ['nullable', 'date'],
            'release_date_precision' => ['nullable', Rule::enum(DatePrecision::class)],
            'metadata' => ['sometimes', 'array'],
        ];
    }

    private function season(): Season
    {
        $season = $this->route('season');

        return $season instanceof Season ? $season : throw new \LogicException('Season route binding is required.');
    }
}
