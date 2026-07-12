<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\EvidenceVisibility;
use App\Enums\PermissionName;
use App\Models\Report;
use App\Models\ReportEvidence;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Report */
class ReportResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $isModerator = $request->user()?->hasPermission(PermissionName::ModerationReportsView) === true;
        $evidence = $this->relationLoaded('evidence') ? $this->evidence->filter(fn (ReportEvidence $item): bool => $isModerator || $item->visibility === EvidenceVisibility::ReporterAndModerators)->map(fn (ReportEvidence $item): array => ['id' => $item->id, 'type' => $item->type->value, 'description' => $item->description, 'media_asset_id' => $item->media_asset_id, 'source_id' => $item->source_id, 'citation_id' => $item->citation_id, 'external_url' => $item->external_url, 'created_at' => $item->created_at?->toISOString()])->values()->all() : [];

        return ['id' => $this->id, 'category' => new ReportCategoryResource($this->whenLoaded('category')), 'target' => ['type' => $this->target_type, 'id' => $this->target_id], 'status' => $this->status->value, 'priority' => $this->priority->value, 'reason_code' => $this->reason_code, 'explanation' => $this->explanation, 'duplicate_of_report_id' => $this->duplicate_of_report_id, 'evidence' => $evidence, 'submitted_at' => $this->submitted_at?->toISOString(), 'withdrawn_at' => $this->withdrawn_at?->toISOString(), 'closed_at' => $this->closed_at?->toISOString(), 'reporter_user_id' => $this->when($isModerator, $this->reporter_user_id)];
    }
}
