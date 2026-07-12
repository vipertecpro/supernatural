<?php

namespace App\Providers;

use App\Enums\PermissionName;
use App\Events\AppealDecided;
use App\Events\AppealSubmitted;
use App\Events\EditorialRevisionApplied;
use App\Events\EditorialRevisionApproved;
use App\Events\LoreEntityPublished;
use App\Events\MediaPublished;
use App\Events\ModerationActionApplied;
use App\Events\ModerationCaseAssigned;
use App\Events\ReportClosed;
use App\Events\ReportSubmitted;
use App\Events\RewatchCycleCompleted;
use App\Events\SearchProjectionRemovalRequested;
use App\Events\SearchProjectionRequested;
use App\Events\TimelinePublished;
use App\Events\UserRestrictionLifted;
use App\Events\ViewingJourneyCompleted;
use App\Listeners\CreateDomainNotification;
use App\Listeners\RefreshSearchProjection;
use App\Models\Appeal;
use App\Models\AppealDecision;
use App\Models\AuditLog;
use App\Models\Citation;
use App\Models\ContentLicense;
use App\Models\ContentRestriction;
use App\Models\EditorialRevision;
use App\Models\EntityAppearance;
use App\Models\Episode;
use App\Models\ExternalEmbed;
use App\Models\Favourite;
use App\Models\Franchise;
use App\Models\LoreAlias;
use App\Models\LoreEntity;
use App\Models\LoreEntityTranslation;
use App\Models\LoreRelationship;
use App\Models\MediaAsset;
use App\Models\MediaAttachment;
use App\Models\MediaProcessingJob;
use App\Models\MediaVariant;
use App\Models\ModerationAction;
use App\Models\ModerationCase;
use App\Models\ModerationCaseAssignment;
use App\Models\NotificationDelivery;
use App\Models\NotificationPreference;
use App\Models\PersonalNote;
use App\Models\Rating;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\ReportEvidence;
use App\Models\RevisionBlock;
use App\Models\RevisionItem;
use App\Models\RewatchCycle;
use App\Models\SearchDocument;
use App\Models\SearchQuery;
use App\Models\SearchSuggestion;
use App\Models\Season;
use App\Models\Source;
use App\Models\SourceRightsReview;
use App\Models\SpoilerBoundary;
use App\Models\SpoilerConstraint;
use App\Models\Timeline;
use App\Models\TimelineEntry;
use App\Models\TrendingSnapshot;
use App\Models\Universe;
use App\Models\User;
use App\Models\UserFandomPreference;
use App\Models\UserNotification;
use App\Models\UserRestriction;
use App\Models\UserRestrictionScope;
use App\Models\UserSpoilerPreference;
use App\Models\UserViewingJourney;
use App\Models\ViewingOrder;
use App\Models\ViewingOrderItem;
use App\Models\ViewingProgress;
use App\Models\ViewingProgressEvent;
use App\Models\ViewingSession;
use App\Models\Watchlist;
use App\Models\WatchlistItem;
use App\Models\Work;
use App\Models\WorkTranslation;
use App\Policies\AppealPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\ContentLicensePolicy;
use App\Policies\EditorialRevisionPolicy;
use App\Policies\EntityAppearancePolicy;
use App\Policies\EpisodePolicy;
use App\Policies\ExternalEmbedPolicy;
use App\Policies\FavouritePolicy;
use App\Policies\FranchisePolicy;
use App\Policies\LoreAliasPolicy;
use App\Policies\LoreEntityPolicy;
use App\Policies\LoreRelationshipPolicy;
use App\Policies\MediaAssetPolicy;
use App\Policies\MediaAttachmentPolicy;
use App\Policies\ModerationCasePolicy;
use App\Policies\NotificationPreferencePolicy;
use App\Policies\PersonalNotePolicy;
use App\Policies\RatingPolicy;
use App\Policies\ReportPolicy;
use App\Policies\RewatchCyclePolicy;
use App\Policies\SeasonPolicy;
use App\Policies\SourcePolicy;
use App\Policies\SourceRightsReviewPolicy;
use App\Policies\SpoilerBoundaryPolicy;
use App\Policies\TimelineEntryPolicy;
use App\Policies\TimelinePolicy;
use App\Policies\UniversePolicy;
use App\Policies\UserFandomPreferencePolicy;
use App\Policies\UserNotificationPolicy;
use App\Policies\UserViewingJourneyPolicy;
use App\Policies\ViewingOrderPolicy;
use App\Policies\ViewingProgressPolicy;
use App\Policies\ViewingSessionPolicy;
use App\Policies\WatchlistPolicy;
use App\Policies\WorkPolicy;
use App\Policies\WorkTranslationPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
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
        $this->configureSearchProjectionListeners();
        $this->configureNotificationListeners();
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
            'media_asset' => MediaAsset::class,
            'media_variant' => MediaVariant::class,
            'external_embed' => ExternalEmbed::class,
            'media_attachment' => MediaAttachment::class,
            'media_processing_job' => MediaProcessingJob::class,
            'search_document' => SearchDocument::class,
            'search_suggestion' => SearchSuggestion::class,
            'trending_snapshot' => TrendingSnapshot::class,
            'search_query' => SearchQuery::class,
            'viewing_order' => ViewingOrder::class,
            'viewing_order_item' => ViewingOrderItem::class,
            'user_viewing_journey' => UserViewingJourney::class,
            'viewing_progress' => ViewingProgress::class,
            'viewing_progress_event' => ViewingProgressEvent::class,
            'viewing_session' => ViewingSession::class,
            'rewatch_cycle' => RewatchCycle::class,
            'watchlist' => Watchlist::class,
            'watchlist_item' => WatchlistItem::class,
            'favourite' => Favourite::class,
            'rating' => Rating::class,
            'personal_note' => PersonalNote::class,
            'user_fandom_preference' => UserFandomPreference::class,
            'user_spoiler_preference' => UserSpoilerPreference::class,
            'report_category' => ReportCategory::class,
            'report' => Report::class,
            'report_evidence' => ReportEvidence::class,
            'moderation_case' => ModerationCase::class,
            'moderation_case_assignment' => ModerationCaseAssignment::class,
            'moderation_action' => ModerationAction::class,
            'user_restriction' => UserRestriction::class,
            'user_restriction_scope' => UserRestrictionScope::class,
            'content_restriction' => ContentRestriction::class,
            'appeal' => Appeal::class,
            'appeal_decision' => AppealDecision::class,
            'user_notification' => UserNotification::class,
            'notification_preference' => NotificationPreference::class,
            'notification_delivery' => NotificationDelivery::class,
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
        Gate::policy(MediaAsset::class, MediaAssetPolicy::class);
        Gate::policy(ExternalEmbed::class, ExternalEmbedPolicy::class);
        Gate::policy(MediaAttachment::class, MediaAttachmentPolicy::class);
        Gate::policy(Season::class, SeasonPolicy::class);
        Gate::policy(Source::class, SourcePolicy::class);
        Gate::policy(SourceRightsReview::class, SourceRightsReviewPolicy::class);
        Gate::policy(SpoilerBoundary::class, SpoilerBoundaryPolicy::class);
        Gate::policy(Universe::class, UniversePolicy::class);
        Gate::policy(Timeline::class, TimelinePolicy::class);
        Gate::policy(TimelineEntry::class, TimelineEntryPolicy::class);
        Gate::policy(Work::class, WorkPolicy::class);
        Gate::policy(WorkTranslation::class, WorkTranslationPolicy::class);
        Gate::policy(UserViewingJourney::class, UserViewingJourneyPolicy::class);
        Gate::policy(ViewingOrder::class, ViewingOrderPolicy::class);
        Gate::policy(ViewingProgress::class, ViewingProgressPolicy::class);
        Gate::policy(ViewingSession::class, ViewingSessionPolicy::class);
        Gate::policy(RewatchCycle::class, RewatchCyclePolicy::class);
        Gate::policy(Watchlist::class, WatchlistPolicy::class);
        Gate::policy(Favourite::class, FavouritePolicy::class);
        Gate::policy(Rating::class, RatingPolicy::class);
        Gate::policy(PersonalNote::class, PersonalNotePolicy::class);
        Gate::policy(UserFandomPreference::class, UserFandomPreferencePolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);
        Gate::policy(ModerationCase::class, ModerationCasePolicy::class);
        Gate::policy(Appeal::class, AppealPolicy::class);
        Gate::policy(UserNotification::class, UserNotificationPolicy::class);
        Gate::policy(NotificationPreference::class, NotificationPreferencePolicy::class);

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

        RateLimiter::for('reports', function (Request $request): Limit {
            return Limit::perMinute((int) config('moderation.report_rate_limit_per_minute', 5))->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip()));
        });

        RateLimiter::for('appeals', function (Request $request): Limit {
            return Limit::perMinute((int) config('moderation.appeal_rate_limit_per_minute', 3))->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip()));
        });
    }

    /** Register synchronous idempotent projection consumers for after-commit events. */
    protected function configureSearchProjectionListeners(): void
    {
        Event::listen([
            SearchProjectionRequested::class,
            SearchProjectionRemovalRequested::class,
            LoreEntityPublished::class,
            TimelinePublished::class,
            EditorialRevisionApplied::class,
        ], RefreshSearchProjection::class);
    }

    /** Register privacy-safe queued notification consumers. */
    protected function configureNotificationListeners(): void
    {
        Event::listen([
            ReportSubmitted::class,
            ReportClosed::class,
            ModerationCaseAssigned::class,
            ModerationActionApplied::class,
            UserRestrictionLifted::class,
            AppealSubmitted::class,
            AppealDecided::class,
            EditorialRevisionApproved::class,
            EditorialRevisionApplied::class,
            MediaPublished::class,
            ViewingJourneyCompleted::class,
            RewatchCycleCompleted::class,
        ], CreateDomainNotification::class);
    }
}
