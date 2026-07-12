<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ReportCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ReportCategory */
class ReportCategoryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return ['key' => $this->key, 'name' => $this->name, 'description' => $this->description, 'applicable_target_types' => $this->applicable_target_types, 'default_priority' => $this->default_priority->value, 'evidence_required' => $this->evidence_required, 'explanation_required' => $this->explanation_required, 'is_active' => $this->is_active];
    }
}
