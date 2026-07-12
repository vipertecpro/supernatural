<?php

namespace Database\Factories;

use App\Enums\EvidenceVisibility;
use App\Enums\ReportEvidenceType;
use App\Models\Report;
use App\Models\ReportEvidence;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ReportEvidence> */
class ReportEvidenceFactory extends Factory
{
    public function definition(): array
    {
        return ['report_id' => Report::factory(), 'created_by_user_id' => User::factory(), 'type' => ReportEvidenceType::Explanation, 'visibility' => EvidenceVisibility::ReporterAndModerators, 'description' => 'A bounded fictional evidence note.', 'safe_metadata' => []];
    }
}
