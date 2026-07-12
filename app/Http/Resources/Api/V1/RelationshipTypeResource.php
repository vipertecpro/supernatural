<?php

namespace App\Http\Resources\Api\V1;

use App\Models\RelationshipType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RelationshipType */
class RelationshipTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'key' => $this->key, 'forward_label' => $this->forward_label, 'inverse_label' => $this->inverse_label, 'direction' => $this->direction->value, 'symmetric' => $this->is_symmetric];
    }
}
