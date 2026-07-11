<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\DatePrecision;
use App\Enums\SeasonType;
use App\Models\Season;
use App\Models\Work;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSeasonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Season::class) && $this->user()->can('update', $this->work());
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type' => ['sometimes', Rule::enum(SeasonType::class)],
            'number' => ['nullable', 'integer', 'min:0', Rule::unique('seasons')->where('work_id', $this->work()->getKey())],
            'display_number' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('seasons')->where('work_id', $this->work()->getKey())],
            'summary' => ['nullable', 'string', 'max:10000'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'original_release_date' => ['nullable', 'date'],
            'release_date_precision' => ['nullable', Rule::enum(DatePrecision::class)],
            'metadata' => ['sometimes', 'array'],
        ];
    }

    private function work(): Work
    {
        $work = $this->route('work');

        return $work instanceof Work ? $work : throw new \LogicException('Work route binding is required.');
    }
}
