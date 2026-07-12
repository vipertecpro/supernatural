<?php

namespace App\Domain\Moderation\Actions;

use App\Domain\Moderation\Exceptions\InvalidModerationOperation;
use App\Domain\Moderation\Services\ReportTargetRegistry;
use App\Enums\EvidenceVisibility;
use App\Enums\ReportEvidenceType;
use App\Enums\ReportStatus;
use App\Events\ReportSubmitted;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\ReportEvidence;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

class ManageReports
{
    public function __construct(private readonly ReportTargetRegistry $targets, private readonly AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $data */
    public function submit(User $reporter, array $data, ?string $requestId): Report
    {
        $category = ReportCategory::query()->where('key', $data['category'])->where('is_active', true)->first();
        if ($category === null) {
            throw new InvalidModerationOperation('The selected report category is unavailable.', 'report_category_unavailable');
        }

        $target = $this->targets->resolve($data['target_type'], (int) $data['target_id']);
        if (! in_array($target->getMorphClass(), $category->applicable_target_types, true) || ! $this->targets->isAccessibleToReporter($target, $reporter)) {
            throw new InvalidModerationOperation('The selected target cannot be reported from this context.', 'report_target_inaccessible');
        }

        $explanation = trim(strip_tags((string) ($data['explanation'] ?? '')));
        if ($category->explanation_required && $explanation === '') {
            throw new InvalidModerationOperation('An explanation is required for this report category.', 'report_explanation_required');
        }

        return DB::transaction(function () use ($reporter, $category, $target, $data, $explanation, $requestId): Report {
            $duplicate = Report::query()->where('reporter_user_id', $reporter->id)->where('report_category_id', $category->id)->where('target_type', $target->getMorphClass())->where('target_id', $target->getKey())->whereIn('status', [ReportStatus::Submitted->value, ReportStatus::Triaged->value, ReportStatus::Linked->value])->latest('id')->first();

            $report = Report::query()->create([
                'reporter_user_id' => $reporter->id,
                'report_category_id' => $category->id,
                'target_type' => $target->getMorphClass(),
                'target_id' => $target->getKey(),
                'duplicate_of_report_id' => $duplicate?->id,
                'status' => $duplicate === null ? ReportStatus::Submitted : ReportStatus::Linked,
                'priority' => $category->default_priority,
                'reason_code' => $data['reason_code'] ?? null,
                'explanation' => $explanation === '' ? null : $explanation,
                'request_id' => $requestId,
                'safe_metadata' => [],
                'submitted_at' => now(),
            ]);

            $this->auditLogger->record('moderation.report_submitted', $report, ['category' => $category->key, 'target_type' => $target->getMorphClass(), 'duplicate_of_report_id' => $duplicate?->id], $reporter, $requestId);
            ReportSubmitted::dispatch($report->id, $reporter->id, $category->key);

            return $report->fresh(['category', 'evidence']);
        });
    }

    public function withdraw(Report $report, User $reporter): Report
    {
        if ($report->reporter_user_id !== $reporter->id || ! in_array($report->status, [ReportStatus::Submitted, ReportStatus::Linked], true)) {
            throw new InvalidModerationOperation('This report can no longer be withdrawn.', 'report_not_withdrawable');
        }

        $report->update(['status' => ReportStatus::Withdrawn, 'withdrawn_at' => now()]);
        $this->auditLogger->record('moderation.report_withdrawn', $report, ['previous_status' => $report->getOriginal('status')], $reporter);

        return $report->fresh('category');
    }

    /** @param array<string, mixed> $data */
    public function addEvidence(Report $report, User $actor, array $data, bool $internal = false): ReportEvidence
    {
        if (! $internal && $report->reporter_user_id !== $actor->id) {
            throw new InvalidModerationOperation('Evidence may be added only to your own report.', 'report_evidence_forbidden');
        }
        if (in_array($report->status, [ReportStatus::Withdrawn, ReportStatus::Closed], true)) {
            throw new InvalidModerationOperation('Evidence cannot be added to a resolved report.', 'report_evidence_closed');
        }

        $description = trim(strip_tags((string) ($data['description'] ?? '')));

        return ReportEvidence::query()->create([
            'report_id' => $report->id,
            'created_by_user_id' => $actor->id,
            'type' => ReportEvidenceType::from($data['type']),
            'visibility' => $internal ? EvidenceVisibility::ModeratorsOnly : EvidenceVisibility::ReporterAndModerators,
            'description' => $description === '' ? null : $description,
            'media_asset_id' => $data['media_asset_id'] ?? null,
            'source_id' => $data['source_id'] ?? null,
            'citation_id' => $data['citation_id'] ?? null,
            'external_url' => $data['external_url'] ?? null,
            'checksum' => isset($data['snapshot']) ? hash('sha256', (string) $data['snapshot']) : null,
            'safe_metadata' => [],
        ]);
    }
}
