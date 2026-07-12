<?php

namespace Database\Seeders;

use App\Enums\ReportPriority;
use App\Models\ReportCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReportCategorySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $targets = ['user', 'universe', 'franchise', 'work', 'work_translation', 'season', 'episode', 'lore_entity', 'lore_alias', 'entity_appearance', 'lore_relationship', 'timeline', 'timeline_entry', 'media_asset', 'external_embed', 'media_attachment', 'viewing_order'];
        $definitions = [
            'spam' => ['Spam', 'Unwanted, deceptive, or repetitive submissions.', ReportPriority::Normal, false, false],
            'harassment' => ['Harassment', 'Targeted abusive or threatening conduct.', ReportPriority::High, false, true],
            'hate_or_abuse' => ['Hate or abusive content', 'Content that attacks or dehumanizes protected groups or individuals.', ReportPriority::High, false, true],
            'sexual_or_exploitative' => ['Sexual or exploitative content', 'Sexual exploitation, non-consensual material, or age-safety concerns.', ReportPriority::Urgent, true, true],
            'illegal_or_dangerous' => ['Illegal or dangerous content', 'Credible illegal, dangerous, or safety-threatening material.', ReportPriority::Urgent, true, true],
            'impersonation' => ['Impersonation', 'Misrepresentation of another person or organization.', ReportPriority::High, true, false],
            'privacy_violation' => ['Privacy violation', 'Exposure or misuse of private or sensitive information.', ReportPriority::Urgent, true, true],
            'copyright_or_ownership' => ['Copyright or ownership concern', 'A concern requiring provenance and rights review without presuming infringement.', ReportPriority::High, true, false],
            'spoiler_violation' => ['Spoiler violation', 'Content that may evade or violate configured spoiler controls.', ReportPriority::Normal, false, false],
            'misleading_factual_content' => ['Misleading factual content', 'Potentially inaccurate or misleading factual material.', ReportPriority::Normal, true, false],
            'rights_or_attribution' => ['Rights or attribution concern', 'A concern about attribution, licensing, or permitted use.', ReportPriority::High, true, false],
            'other' => ['Other', 'A concern that does not fit another controlled category.', ReportPriority::Normal, false, false],
        ];

        foreach ($definitions as $key => [$name, $description, $priority, $evidenceRequired, $safetyReview]) {
            ReportCategory::query()->updateOrCreate(
                ['key' => $key],
                ['name' => $name, 'description' => $description, 'applicable_target_types' => $targets, 'default_priority' => $priority, 'evidence_required' => $evidenceRequired, 'explanation_required' => true, 'rights_review_required' => in_array($key, ['copyright_or_ownership', 'rights_or_attribution'], true), 'safety_review_required' => $safetyReview, 'appeals_supported' => true, 'is_active' => true, 'safe_metadata' => []],
            );
        }
    }
}
