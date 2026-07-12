<?php

namespace App\Providers;

use App\Enums\PermissionName;
use App\Models\AuditLog;
use App\Models\Citation;
use App\Models\ContentLicense;
use App\Models\EditorialRevision;
use App\Models\EntityAppearance;
use App\Models\Episode;
use App\Models\Franchise;
use App\Models\LoreAlias;
use App\Models\LoreEntity;
use App\Models\LoreEntityTranslation;
use App\Models\LoreRelationship;
use App\Models\RevisionBlock;
use App\Models\RevisionItem;
use App\Models\Season;
use App\Models\Source;
use App\Models\SourceRightsReview;
use App\Models\SpoilerBoundary;
use App\Models\SpoilerConstraint;
use App\Models\Timeline;
use App\Models\TimelineEntry;
use App\Models\Universe;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkTranslation;
use App\Policies\AuditLogPolicy;
use App\Policies\ContentLicensePolicy;
use App\Policies\EditorialRevisionPolicy;
use App\Policies\EntityAppearancePolicy;
use App\Policies\EpisodePolicy;
use App\Policies\FranchisePolicy;
use App\Policies\LoreAliasPolicy;
use App\Policies\LoreEntityPolicy;
use App\Policies\LoreRelationshipPolicy;
use App\Policies\SeasonPolicy;
use App\Policies\SourcePolicy;
use App\Policies\SourceRightsReviewPolicy;
use App\Policies\SpoilerBoundaryPolicy;
use App\Policies\TimelineEntryPolicy;
use App\Policies\TimelinePolicy;
use App\Policies\UniversePolicy;
use App\Policies\WorkPolicy;
use App\Policies\WorkTranslationPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureMorphMap();
        $this->configureAuthorization();
        $this->configureRateLimiting();
    }

    /** Keep polymorphic persistence stable across PHP namespace changes. */
    protected function configureMorphMap(): void
    {
        Relation::enforceMorphMap([
            'user' => User::class,
            'universe' => Universe::class,
            'source' => Source::class,
            'content_license' => ContentLicense::class,
            'franchise' => Franchise::class,
            'work' => Work::class,
            'work_translation' => WorkTranslation::class,
            'season' => Season::class,
            'episode' => Episode::class,
            'editorial_revision' => EditorialRevision::class,
            'revision_item' => RevisionItem::class,
            'revision_block' => RevisionBlock::class,
            'source_rights_review' => SourceRightsReview::class,
            'spoiler_boundary' => SpoilerBoundary::class,
            'spoiler_constraint' => SpoilerConstraint::class,
            'citation' => Citation::class,
            'lore_entity' => LoreEntity::class,
            'lore_entity_translation' => LoreEntityTranslation::class,
            'lore_alias' => LoreAlias::class,
            'entity_appearance' => EntityAppearance::class,
            'lore_relationship' => LoreRelationship::class,
            'timeline' => Timeline::class,
            'timeline_entry' => TimelineEntry::class,
        ]);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /** Configure first-party role and permission authorization. */
    protected function configureAuthorization(): void
    {
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(ContentLicense::class, ContentLicensePolicy::class);
        Gate::policy(Episode::class, EpisodePolicy::class);
        Gate::policy(EntityAppearance::class, EntityAppearancePolicy::class);
        Gate::policy(EditorialRevision::class, EditorialRevisionPolicy::class);
        Gate::policy(Franchise::class, FranchisePolicy::class);
        Gate::policy(LoreAlias::class, LoreAliasPolicy::class);
        Gate::policy(LoreEntity::class, LoreEntityPolicy::class);
        Gate::policy(LoreRelationship::class, LoreRelationshipPolicy::class);
        Gate::policy(Season::class, SeasonPolicy::class);
        Gate::policy(Source::class, SourcePolicy::class);
        Gate::policy(SourceRightsReview::class, SourceRightsReviewPolicy::class);
        Gate::policy(SpoilerBoundary::class, SpoilerBoundaryPolicy::class);
        Gate::policy(Universe::class, UniversePolicy::class);
        Gate::policy(Timeline::class, TimelinePolicy::class);
        Gate::policy(TimelineEntry::class, TimelineEntryPolicy::class);
        Gate::policy(Work::class, WorkPolicy::class);
        Gate::policy(WorkTranslation::class, WorkTranslationPolicy::class);

        foreach (PermissionName::cases() as $permission) {
            Gate::define(
                $permission->value,
                fn (User $user): bool => $user->hasPermission($permission),
            );
        }
    }

    /** Configure API rate limits without retaining request-specific state. */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api-v1', function (Request $request): Limit {
            return Limit::perMinute((int) config('api.rate_limit_per_minute', 60))
                ->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip()));
        });

        RateLimiter::for('api-v1-public', function (Request $request): Limit {
            return Limit::perMinute((int) config('api.public_rate_limit_per_minute', 30))
                ->by((string) $request->ip());
        });
    }
}
