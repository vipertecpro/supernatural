<?php

use App\Http\Controllers\Api\V1\AppealController;
use App\Http\Controllers\Api\V1\BunkerController;
use App\Http\Controllers\Api\V1\CommunityInteractionController;
use App\Http\Controllers\Api\V1\CommunityPostController;
use App\Http\Controllers\Api\V1\ContinueWatchingController;
use App\Http\Controllers\Api\V1\EditorialCitationController;
use App\Http\Controllers\Api\V1\EditorialReviewController;
use App\Http\Controllers\Api\V1\EditorialRevisionController;
use App\Http\Controllers\Api\V1\EntityAppearanceController;
use App\Http\Controllers\Api\V1\EpisodeController;
use App\Http\Controllers\Api\V1\ExternalEmbedController;
use App\Http\Controllers\Api\V1\FavouriteController;
use App\Http\Controllers\Api\V1\FranchiseController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\InteractionSafetyController;
use App\Http\Controllers\Api\V1\JourneyPreferenceController;
use App\Http\Controllers\Api\V1\LoreAliasController;
use App\Http\Controllers\Api\V1\LoreEntityController;
use App\Http\Controllers\Api\V1\LoreRelationshipController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\MediaAssetController;
use App\Http\Controllers\Api\V1\MediaAttachmentController;
use App\Http\Controllers\Api\V1\ModerationCaseController;
use App\Http\Controllers\Api\V1\NotificationPreferenceController;
use App\Http\Controllers\Api\V1\PersonalNoteController;
use App\Http\Controllers\Api\V1\RatingController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\RewatchCycleController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\SeasonController;
use App\Http\Controllers\Api\V1\SourceRightsReviewController;
use App\Http\Controllers\Api\V1\SpoilerBoundaryController;
use App\Http\Controllers\Api\V1\TimelineController;
use App\Http\Controllers\Api\V1\TimelineEntryController;
use App\Http\Controllers\Api\V1\UserNotificationController;
use App\Http\Controllers\Api\V1\ViewingJourneyController;
use App\Http\Controllers\Api\V1\ViewingOrderController;
use App\Http\Controllers\Api\V1\ViewingProgressController;
use App\Http\Controllers\Api\V1\ViewingSessionController;
use App\Http\Controllers\Api\V1\WatchlistController;
use App\Http\Controllers\Api\V1\WorkController;
use App\Http\Controllers\Api\V1\WorkTranslationController;
use App\Http\Middleware\ResolveOptionalSanctumUser;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('health', HealthController::class)
        ->middleware('throttle:api-v1-public')
        ->name('health');

    Route::get('me', MeController::class)
        ->middleware(['auth:sanctum', 'verified', 'throttle:api-v1'])
        ->name('me');

    Route::middleware(['throttle:api-v1-public', ResolveOptionalSanctumUser::class])->scopeBindings()->group(function () {
        Route::get('universes/{universe}/franchises', [FranchiseController::class, 'index'])->name('universes.franchises.index');
        Route::get('franchises/{franchise}', [FranchiseController::class, 'show'])->name('franchises.show');
        Route::get('universes/{universe}/works', [WorkController::class, 'index'])->name('universes.works.index');
        Route::get('works/{work}', [WorkController::class, 'show'])->name('works.show');
        Route::get('works/{work}/seasons', [SeasonController::class, 'index'])->name('works.seasons.index');
        Route::get('seasons/{season}', [SeasonController::class, 'show'])->name('seasons.show');
        Route::get('seasons/{season}/episodes', [EpisodeController::class, 'index'])->name('seasons.episodes.index');
        Route::get('episodes/{episode}', [EpisodeController::class, 'show'])->name('episodes.show');
        Route::get('universes/{universe}/lore', [LoreEntityController::class, 'index'])->name('universes.lore.index');
        Route::get('lore/{entity}', [LoreEntityController::class, 'show'])->name('lore.show');
        Route::get('lore/{entity}/aliases', [LoreAliasController::class, 'index'])->name('lore.aliases.index');
        Route::get('lore/{entity}/appearances', [EntityAppearanceController::class, 'index'])->name('lore.appearances.index');
        Route::get('lore/{entity}/relationships', [LoreRelationshipController::class, 'index'])->name('lore.relationships.index');
        Route::get('lore/{entity}/timeline-entries', [TimelineEntryController::class, 'forEntity'])->name('lore.timeline-entries.index');
        Route::get('universes/{universe}/timelines', [TimelineController::class, 'index'])->name('universes.timelines.index');
        Route::get('timelines/{timeline}', [TimelineController::class, 'show'])->name('timelines.show');
        Route::get('timelines/{timeline}/entries', [TimelineEntryController::class, 'index'])->name('timelines.entries.index');
        Route::get('search', [SearchController::class, 'index'])->name('search.index');
        Route::get('search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');
        Route::get('discovery/related/{type}/{id}', [SearchController::class, 'related'])->whereNumber('id')->name('discovery.related');
        Route::get('media/assets/{asset}', [MediaAssetController::class, 'show'])->name('media.assets.show');
        Route::get('media/embeds/{embed}', [ExternalEmbedController::class, 'show'])->name('media.embeds.show');
        Route::get('media/attachments/{targetType}/{targetId}', [MediaAttachmentController::class, 'index'])->whereNumber('targetId')->name('media.attachments.index');
        Route::get('universes/{universe}/viewing-orders', [ViewingOrderController::class, 'index'])->name('universes.viewing-orders.index');
        Route::get('viewing-orders/{viewingOrder}', [ViewingOrderController::class, 'show'])->name('viewing-orders.show');
        Route::get('viewing-orders/{viewingOrder}/items', [ViewingOrderController::class, 'items'])->name('viewing-orders.items');
        Route::get('bunker-categories', [BunkerController::class, 'categories'])->name('bunker-categories.index');
        Route::get('universes/{universe}/bunkers', [BunkerController::class, 'index'])->name('universes.bunkers.index');
        Route::get('bunkers/{bunker}', [BunkerController::class, 'show'])->name('bunkers.show');
        Route::get('bunkers/{bunker}/rules', [BunkerController::class, 'rules'])->name('bunkers.rules.index');
        Route::get('bunkers/{bunker}/members', [BunkerController::class, 'members'])->name('bunkers.members.index');
        Route::get('community/feed', [CommunityPostController::class, 'feed'])->name('community.feed');
        Route::get('universes/{universe}/community/feed', [CommunityPostController::class, 'universeFeed'])->name('universes.community.feed');
        Route::get('bunkers/{bunker}/feed', [CommunityPostController::class, 'bunkerFeed'])->name('bunkers.feed');
        Route::get('community/posts/{post}', [CommunityPostController::class, 'show'])->name('community.posts.show');
        Route::get('community/posts/{post}/comments', [CommunityPostController::class, 'comments'])->name('community.posts.comments.index');
    });

    Route::middleware(['auth:sanctum', 'verified', 'throttle:api-v1', 'restrictions'])->scopeBindings()->group(function () {
        Route::get('me/blocks', [InteractionSafetyController::class, 'blocks'])->name('me.blocks.index');
        Route::post('me/blocks', [InteractionSafetyController::class, 'block'])->middleware('throttle:interaction-safety')->name('me.blocks.store');
        Route::delete('me/blocks/{block}', [InteractionSafetyController::class, 'unblock'])->name('me.blocks.destroy');
        Route::get('me/mutes', [InteractionSafetyController::class, 'mutes'])->name('me.mutes.index');
        Route::post('me/mutes', [InteractionSafetyController::class, 'mute'])->middleware('throttle:interaction-safety')->name('me.mutes.store');
        Route::delete('me/mutes/{mute}', [InteractionSafetyController::class, 'unmute'])->name('me.mutes.destroy');
        Route::get('report-categories', [ReportController::class, 'categories'])->name('report-categories.index');
        Route::get('me/reports', [ReportController::class, 'index'])->name('me.reports.index');
        Route::post('reports', [ReportController::class, 'store'])->middleware('throttle:reports')->name('reports.store');
        Route::get('me/reports/{report}', [ReportController::class, 'show'])->name('me.reports.show');
        Route::post('me/reports/{report}/withdraw', [ReportController::class, 'withdraw'])->name('me.reports.withdraw');
        Route::post('me/reports/{report}/evidence', [ReportController::class, 'evidence'])->name('me.reports.evidence.store');

        Route::get('me/appeals', [AppealController::class, 'index'])->name('me.appeals.index');
        Route::post('appeals', [AppealController::class, 'store'])->middleware('throttle:appeals')->name('me.appeals.store');
        Route::get('me/appeals/{appeal}', [AppealController::class, 'show'])->name('me.appeals.show');
        Route::post('me/appeals/{appeal}/withdraw', [AppealController::class, 'withdraw'])->name('me.appeals.withdraw');

        Route::get('me/notifications', [UserNotificationController::class, 'index'])->name('me.notifications.index');
        Route::post('me/notifications/read-all', [UserNotificationController::class, 'readAll'])->name('me.notifications.read-all');
        Route::get('me/notifications/{notification}', [UserNotificationController::class, 'show'])->name('me.notifications.show');
        Route::post('me/notifications/{notification}/read', [UserNotificationController::class, 'read'])->name('me.notifications.read');
        Route::post('me/notifications/{notification}/unread', [UserNotificationController::class, 'unread'])->name('me.notifications.unread');
        Route::post('me/notifications/{notification}/archive', [UserNotificationController::class, 'archive'])->name('me.notifications.archive');
        Route::get('me/notification-preferences', [NotificationPreferenceController::class, 'index'])->name('me.notification-preferences.index');
        Route::patch('me/notification-preferences', [NotificationPreferenceController::class, 'update'])->name('me.notification-preferences.update');

        Route::post('universes/{universe}/bunkers', [BunkerController::class, 'store'])->middleware('throttle:bunker-create')->name('universes.bunkers.store');
        Route::patch('bunkers/{bunker}', [BunkerController::class, 'update'])->name('bunkers.update');
        Route::post('bunkers/{bunker}/publish', [BunkerController::class, 'publish'])->name('bunkers.publish');
        Route::post('bunkers/{bunker}/archive', [BunkerController::class, 'archive'])->name('bunkers.archive');
        Route::post('bunkers/{bunker}/transfer-ownership', [BunkerController::class, 'transfer'])->name('bunkers.transfer-ownership');
        Route::post('bunkers/{bunker}/join-requests', [BunkerController::class, 'join'])->middleware('throttle:bunker-create')->name('bunkers.join-requests.store');
        Route::post('bunker-join-requests/{joinRequest}/approve', [BunkerController::class, 'approveJoin'])->name('bunker-join-requests.approve');
        Route::post('bunker-join-requests/{joinRequest}/reject', [BunkerController::class, 'rejectJoin'])->name('bunker-join-requests.reject');
        Route::post('bunker-join-requests/{joinRequest}/withdraw', [BunkerController::class, 'withdrawJoin'])->name('bunker-join-requests.withdraw');
        Route::post('bunkers/{bunker}/invitations', [BunkerController::class, 'invite'])->name('bunkers.invitations.store');
        Route::post('bunker-invitations/{invitation}/accept', [BunkerController::class, 'acceptInvitation'])->name('bunker-invitations.accept');
        Route::post('bunker-invitations/{invitation}/decline', [BunkerController::class, 'declineInvitation'])->name('bunker-invitations.decline');
        Route::post('bunker-invitations/{invitation}/revoke', [BunkerController::class, 'revokeInvitation'])->name('bunker-invitations.revoke');
        Route::get('me/bunker-memberships', [BunkerController::class, 'myMemberships'])->name('me.bunker-memberships.index');
        Route::patch('bunker-memberships/{membership}', [BunkerController::class, 'updateMembership'])->name('bunker-memberships.update');
        Route::delete('bunker-memberships/{membership}', [BunkerController::class, 'removeMembership'])->name('bunker-memberships.destroy');
        Route::post('bunkers/{bunker}/bans', [BunkerController::class, 'ban'])->name('bunkers.bans.store');
        Route::post('bunker-bans/{ban}/lift', [BunkerController::class, 'liftBan'])->name('bunker-bans.lift');
        Route::post('bunkers/{bunker}/rules', [BunkerController::class, 'storeRule'])->name('bunkers.rules.store');
        Route::patch('bunker-rules/{rule}', [BunkerController::class, 'updateRule'])->name('bunker-rules.update');
        Route::delete('bunker-rules/{rule}', [BunkerController::class, 'deleteRule'])->name('bunker-rules.destroy');
        Route::post('bunkers/{bunker}/rules/reorder', [BunkerController::class, 'reorderRules'])->name('bunkers.rules.reorder');

        Route::post('community/posts', [CommunityPostController::class, 'store'])->middleware('throttle:community-posts')->name('community.posts.store');
        Route::patch('community/posts/{post}', [CommunityPostController::class, 'update'])->name('community.posts.update');
        Route::delete('community/posts/{post}', [CommunityPostController::class, 'destroy'])->name('community.posts.destroy');
        Route::post('community/posts/{post}/lock', [CommunityPostController::class, 'lock'])->name('community.posts.lock');
        Route::post('community/posts/{post}/unlock', [CommunityPostController::class, 'unlock'])->name('community.posts.unlock');
        Route::post('community/posts/{post}/comments', [CommunityPostController::class, 'storeComment'])->middleware('throttle:community-comments')->name('community.posts.comments.store');
        Route::patch('community/comments/{comment}', [CommunityPostController::class, 'updateComment'])->name('community.comments.update');
        Route::delete('community/comments/{comment}', [CommunityPostController::class, 'destroyComment'])->name('community.comments.destroy');
        Route::put('community/{type}/{id}/reactions/{reaction}', [CommunityInteractionController::class, 'react'])->whereIn('type', ['post', 'comment'])->whereIn('reaction', ['like', 'love', 'insightful', 'funny', 'support'])->whereNumber('id')->middleware('throttle:community-interactions')->name('community.reactions.store');
        Route::delete('community/{type}/{id}/reactions/{reaction}', [CommunityInteractionController::class, 'unreact'])->whereIn('type', ['post', 'comment'])->whereIn('reaction', ['like', 'love', 'insightful', 'funny', 'support'])->whereNumber('id')->name('community.reactions.destroy');
        Route::get('me/community-bookmarks', [CommunityInteractionController::class, 'bookmarks'])->name('me.community-bookmarks.index');
        Route::post('me/community-bookmarks/{post}', [CommunityInteractionController::class, 'bookmark'])->name('me.community-bookmarks.store');
        Route::delete('me/community-bookmarks/{bookmark}', [CommunityInteractionController::class, 'removeBookmark'])->name('me.community-bookmarks.destroy');
        Route::post('community/posts/{post}/poll', [CommunityInteractionController::class, 'createPoll'])->name('community.polls.store');
        Route::post('community/polls/{poll}/votes', [CommunityInteractionController::class, 'vote'])->name('community.polls.votes.store');
        Route::delete('community/polls/{poll}/votes', [CommunityInteractionController::class, 'removeVote'])->name('community.polls.votes.destroy');
        Route::post('community/polls/{poll}/close', [CommunityInteractionController::class, 'close'])->name('community.polls.close');

        Route::prefix('moderation')->name('moderation.')->group(function () {
            Route::get('cases', [ModerationCaseController::class, 'index'])->name('cases.index');
            Route::post('cases', [ModerationCaseController::class, 'store'])->name('cases.store');
            Route::get('cases/{case}', [ModerationCaseController::class, 'show'])->name('cases.show');
            Route::patch('cases/{case}', [ModerationCaseController::class, 'update'])->name('cases.update');
            Route::post('cases/{case}/assign', [ModerationCaseController::class, 'assign'])->name('cases.assign');
            Route::post('cases/{case}/actions', [ModerationCaseController::class, 'action'])->name('cases.actions.store');
            Route::post('user-restrictions/{restriction}/lift', [ModerationCaseController::class, 'liftUserRestriction'])->name('user-restrictions.lift');
            Route::post('content-restrictions/{restriction}/lift', [ModerationCaseController::class, 'liftContentRestriction'])->name('content-restrictions.lift');
            Route::get('appeals', [AppealController::class, 'moderationIndex'])->name('appeals.index');
            Route::get('appeals/{appeal}', [AppealController::class, 'moderationShow'])->name('appeals.show');
            Route::post('appeals/{appeal}/decide', [AppealController::class, 'decide'])->name('appeals.decide');
        });

        Route::get('me/journeys', [ViewingJourneyController::class, 'index'])->name('me.journeys.index');
        Route::post('me/journeys', [ViewingJourneyController::class, 'store'])->name('me.journeys.store');
        Route::get('me/journeys/{journey}', [ViewingJourneyController::class, 'show'])->name('me.journeys.show');
        Route::post('me/journeys/{journey}/pause', [ViewingJourneyController::class, 'pause'])->name('me.journeys.pause');
        Route::post('me/journeys/{journey}/resume', [ViewingJourneyController::class, 'resume'])->name('me.journeys.resume');
        Route::post('me/journeys/{journey}/complete', [ViewingJourneyController::class, 'complete'])->name('me.journeys.complete');
        Route::post('me/journeys/{journey}/abandon', [ViewingJourneyController::class, 'abandon'])->name('me.journeys.abandon');

        Route::get('me/progress', [ViewingProgressController::class, 'index'])->name('me.progress.index');
        Route::get('me/progress/{type}/{id}', [ViewingProgressController::class, 'show'])->whereIn('type', ['work', 'season', 'episode'])->whereNumber('id')->name('me.progress.show');
        Route::put('me/progress/{type}/{id}', [ViewingProgressController::class, 'update'])->whereIn('type', ['work', 'season', 'episode'])->whereNumber('id')->name('me.progress.update');
        Route::post('me/progress/{type}/{id}/complete', [ViewingProgressController::class, 'complete'])->whereIn('type', ['work', 'season', 'episode'])->whereNumber('id')->name('me.progress.complete');
        Route::post('me/progress/{type}/{id}/correct', [ViewingProgressController::class, 'correct'])->whereIn('type', ['work', 'season', 'episode'])->whereNumber('id')->name('me.progress.correct');
        Route::post('me/progress/{type}/{id}/reset', [ViewingProgressController::class, 'reset'])->whereIn('type', ['work', 'season', 'episode'])->whereNumber('id')->name('me.progress.reset');

        Route::post('me/viewing-sessions', [ViewingSessionController::class, 'store'])->name('me.viewing-sessions.store');
        Route::patch('me/viewing-sessions/{session}', [ViewingSessionController::class, 'update'])->name('me.viewing-sessions.update');
        Route::post('me/viewing-sessions/{session}/end', [ViewingSessionController::class, 'end'])->name('me.viewing-sessions.end');
        Route::get('me/rewatches', [RewatchCycleController::class, 'index'])->name('me.rewatches.index');
        Route::post('me/rewatches', [RewatchCycleController::class, 'store'])->name('me.rewatches.store');
        Route::post('me/rewatches/{rewatch}/complete', [RewatchCycleController::class, 'complete'])->name('me.rewatches.complete');
        Route::post('me/rewatches/{rewatch}/abandon', [RewatchCycleController::class, 'abandon'])->name('me.rewatches.abandon');
        Route::get('me/continue-watching', ContinueWatchingController::class)->name('me.continue-watching');

        Route::get('me/watchlists', [WatchlistController::class, 'index'])->name('me.watchlists.index');
        Route::post('me/watchlists', [WatchlistController::class, 'store'])->name('me.watchlists.store');
        Route::get('me/watchlists/{watchlist}', [WatchlistController::class, 'show'])->name('me.watchlists.show');
        Route::patch('me/watchlists/{watchlist}', [WatchlistController::class, 'update'])->name('me.watchlists.update');
        Route::delete('me/watchlists/{watchlist}', [WatchlistController::class, 'destroy'])->name('me.watchlists.destroy');
        Route::post('me/watchlists/{watchlist}/items', [WatchlistController::class, 'addItem'])->name('me.watchlists.items.store');
        Route::delete('me/watchlist-items/{item}', [WatchlistController::class, 'removeItem'])->name('me.watchlist-items.destroy');

        Route::get('me/favourites', [FavouriteController::class, 'index'])->name('me.favourites.index');
        Route::post('me/favourites', [FavouriteController::class, 'store'])->name('me.favourites.store');
        Route::delete('me/favourites/{favourite}', [FavouriteController::class, 'destroy'])->name('me.favourites.destroy');
        Route::get('me/ratings', [RatingController::class, 'index'])->name('me.ratings.index');
        Route::put('me/ratings/{type}/{id}', [RatingController::class, 'upsert'])->whereIn('type', ['work', 'season', 'episode'])->whereNumber('id')->name('me.ratings.upsert');
        Route::delete('me/ratings/{rating}', [RatingController::class, 'destroy'])->name('me.ratings.destroy');
        Route::get('me/notes', [PersonalNoteController::class, 'index'])->name('me.notes.index');
        Route::post('me/notes', [PersonalNoteController::class, 'store'])->name('me.notes.store');
        Route::get('me/notes/{note}', [PersonalNoteController::class, 'show'])->name('me.notes.show');
        Route::patch('me/notes/{note}', [PersonalNoteController::class, 'update'])->name('me.notes.update');
        Route::delete('me/notes/{note}', [PersonalNoteController::class, 'destroy'])->name('me.notes.destroy');
        Route::get('me/journey-preferences', [JourneyPreferenceController::class, 'index'])->name('me.journey-preferences.index');
        Route::patch('me/journey-preferences', [JourneyPreferenceController::class, 'update'])->name('me.journey-preferences.update');

        Route::post('media/assets', [MediaAssetController::class, 'store'])->name('media.assets.store');
        Route::patch('media/assets/{asset}', [MediaAssetController::class, 'update'])->name('media.assets.update');
        Route::post('media/assets/{asset}/publish', [MediaAssetController::class, 'publish'])->name('media.assets.publish');
        Route::post('media/assets/{asset}/archive', [MediaAssetController::class, 'archive'])->name('media.assets.archive');
        Route::post('media/embeds', [ExternalEmbedController::class, 'store'])->name('media.embeds.store');
        Route::patch('media/embeds/{embed}', [ExternalEmbedController::class, 'update'])->name('media.embeds.update');
        Route::post('media/embeds/{embed}/publish', [ExternalEmbedController::class, 'publish'])->name('media.embeds.publish');
        Route::post('media/embeds/{embed}/archive', [ExternalEmbedController::class, 'archive'])->name('media.embeds.archive');
        Route::post('media/attachments', [MediaAttachmentController::class, 'store'])->name('media.attachments.store');
        Route::post('media/attachments/{attachment}/publish', [MediaAttachmentController::class, 'publish'])->name('media.attachments.publish');
        Route::delete('media/attachments/{attachment}', [MediaAttachmentController::class, 'destroy'])->name('media.attachments.destroy');
        Route::post('universes/{universe}/franchises', [FranchiseController::class, 'store'])->name('universes.franchises.store');
        Route::patch('franchises/{franchise}', [FranchiseController::class, 'update'])->name('franchises.update');
        Route::post('franchises/{franchise}/publish', [FranchiseController::class, 'publish'])->name('franchises.publish');
        Route::post('franchises/{franchise}/archive', [FranchiseController::class, 'archive'])->name('franchises.archive');
        Route::delete('franchises/{franchise}', [FranchiseController::class, 'destroy'])->name('franchises.destroy');

        Route::post('universes/{universe}/works', [WorkController::class, 'store'])->name('universes.works.store');
        Route::patch('works/{work}', [WorkController::class, 'update'])->name('works.update');
        Route::post('works/{work}/publish', [WorkController::class, 'publish'])->name('works.publish');
        Route::post('works/{work}/archive', [WorkController::class, 'archive'])->name('works.archive');
        Route::delete('works/{work}', [WorkController::class, 'destroy'])->name('works.destroy');

        Route::post('works/{work}/translations', [WorkTranslationController::class, 'store'])->name('works.translations.store');
        Route::patch('works/{work}/translations/{locale}', [WorkTranslationController::class, 'update'])->name('works.translations.update');
        Route::post('works/{work}/translations/{locale}/publish', [WorkTranslationController::class, 'publish'])->name('works.translations.publish');

        Route::post('works/{work}/seasons', [SeasonController::class, 'store'])->name('works.seasons.store');
        Route::patch('seasons/{season}', [SeasonController::class, 'update'])->name('seasons.update');
        Route::post('seasons/{season}/publish', [SeasonController::class, 'publish'])->name('seasons.publish');
        Route::post('seasons/{season}/archive', [SeasonController::class, 'archive'])->name('seasons.archive');
        Route::delete('seasons/{season}', [SeasonController::class, 'destroy'])->name('seasons.destroy');

        Route::post('seasons/{season}/episodes', [EpisodeController::class, 'store'])->name('seasons.episodes.store');
        Route::patch('episodes/{episode}', [EpisodeController::class, 'update'])->name('episodes.update');
        Route::post('episodes/{episode}/publish', [EpisodeController::class, 'publish'])->name('episodes.publish');
        Route::post('episodes/{episode}/archive', [EpisodeController::class, 'archive'])->name('episodes.archive');
        Route::delete('episodes/{episode}', [EpisodeController::class, 'destroy'])->name('episodes.destroy');

        Route::post('universes/{universe}/lore', [LoreEntityController::class, 'store'])->name('universes.lore.store');
        Route::patch('lore/{entity}', [LoreEntityController::class, 'update'])->name('lore.update');
        Route::post('lore/{entity}/publish', [LoreEntityController::class, 'publish'])->name('lore.publish');
        Route::post('lore/{entity}/archive', [LoreEntityController::class, 'archive'])->name('lore.archive');
        Route::delete('lore/{entity}', [LoreEntityController::class, 'destroy'])->name('lore.destroy');
        Route::post('lore/{entity}/translations', [LoreEntityController::class, 'storeTranslation'])->name('lore.translations.store');
        Route::patch('lore-translations/{translation}', [LoreEntityController::class, 'updateTranslation'])->name('lore-translations.update');
        Route::post('lore-translations/{translation}/publish', [LoreEntityController::class, 'publishTranslation'])->name('lore-translations.publish');

        Route::post('lore/{entity}/aliases', [LoreAliasController::class, 'store'])->name('lore.aliases.store');
        Route::patch('lore-aliases/{alias}', [LoreAliasController::class, 'update'])->name('lore-aliases.update');
        Route::post('lore-aliases/{alias}/publish', [LoreAliasController::class, 'publish'])->name('lore-aliases.publish');
        Route::post('lore-aliases/{alias}/archive', [LoreAliasController::class, 'archive'])->name('lore-aliases.archive');

        Route::post('lore/{entity}/appearances', [EntityAppearanceController::class, 'store'])->name('lore.appearances.store');
        Route::patch('lore-appearances/{appearance}', [EntityAppearanceController::class, 'update'])->name('lore-appearances.update');
        Route::post('lore-appearances/{appearance}/publish', [EntityAppearanceController::class, 'publish'])->name('lore-appearances.publish');
        Route::post('lore-appearances/{appearance}/archive', [EntityAppearanceController::class, 'archive'])->name('lore-appearances.archive');

        Route::post('lore-relationships', [LoreRelationshipController::class, 'store'])->name('lore-relationships.store');
        Route::patch('lore-relationships/{relationship}', [LoreRelationshipController::class, 'update'])->name('lore-relationships.update');
        Route::post('lore-relationships/{relationship}/publish', [LoreRelationshipController::class, 'publish'])->name('lore-relationships.publish');
        Route::post('lore-relationships/{relationship}/archive', [LoreRelationshipController::class, 'archive'])->name('lore-relationships.archive');

        Route::post('universes/{universe}/timelines', [TimelineController::class, 'store'])->name('universes.timelines.store');
        Route::patch('timelines/{timeline}', [TimelineController::class, 'update'])->name('timelines.update');
        Route::post('timelines/{timeline}/publish', [TimelineController::class, 'publish'])->name('timelines.publish');
        Route::post('timelines/{timeline}/archive', [TimelineController::class, 'archive'])->name('timelines.archive');
        Route::post('timelines/{timeline}/entries', [TimelineEntryController::class, 'store'])->name('timelines.entries.store');
        Route::patch('timeline-entries/{entry}', [TimelineEntryController::class, 'update'])->name('timeline-entries.update');
        Route::post('timeline-entries/{entry}/publish', [TimelineEntryController::class, 'publish'])->name('timeline-entries.publish');
        Route::post('timeline-entries/{entry}/archive', [TimelineEntryController::class, 'archive'])->name('timeline-entries.archive');

        Route::prefix('editorial')->name('editorial.')->group(function () {
            Route::get('revisions', [EditorialRevisionController::class, 'index'])->name('revisions.index');
            Route::post('revisions', [EditorialRevisionController::class, 'store'])->name('revisions.store');
            Route::get('revisions/{revision}', [EditorialRevisionController::class, 'show'])->name('revisions.show');
            Route::patch('revisions/{revision}', [EditorialRevisionController::class, 'update'])->name('revisions.update');
            Route::post('revisions/{revision}/items', [EditorialRevisionController::class, 'storeItem'])->name('revisions.items.store');
            Route::patch('revisions/{revision}/items/{item}', [EditorialRevisionController::class, 'updateItem'])->name('revisions.items.update');
            Route::delete('revisions/{revision}/items/{item}', [EditorialRevisionController::class, 'destroyItem'])->name('revisions.items.destroy');
            Route::post('revisions/{revision}/blocks', [EditorialRevisionController::class, 'storeBlock'])->name('revisions.blocks.store');
            Route::delete('revisions/{revision}/blocks/{block}', [EditorialRevisionController::class, 'destroyBlock'])->name('revisions.blocks.destroy');
            Route::post('revisions/{revision}/submit', [EditorialRevisionController::class, 'submit'])->name('revisions.submit');
            Route::post('revisions/{revision}/withdraw', [EditorialRevisionController::class, 'withdraw'])->name('revisions.withdraw');
            Route::post('revisions/{revision}/resubmit', [EditorialRevisionController::class, 'resubmit'])->name('revisions.resubmit');

            Route::post('revisions/{revision}/assign', [EditorialReviewController::class, 'assign'])->name('revisions.assign');
            Route::post('revisions/{revision}/assignments/{assignment}/cancel', [EditorialReviewController::class, 'cancelAssignment'])->name('revisions.assignments.cancel');
            Route::post('revisions/{revision}/begin-review', [EditorialReviewController::class, 'begin'])->name('revisions.begin-review');
            Route::post('revisions/{revision}/request-changes', [EditorialReviewController::class, 'requestChanges'])->name('revisions.request-changes');
            Route::post('revisions/{revision}/approve', [EditorialReviewController::class, 'approve'])->name('revisions.approve');
            Route::post('revisions/{revision}/reject', [EditorialReviewController::class, 'reject'])->name('revisions.reject');
            Route::post('revisions/{revision}/apply', [EditorialReviewController::class, 'apply'])->name('revisions.apply');

            Route::get('revisions/{revision}/citations', [EditorialCitationController::class, 'index'])->name('revisions.citations.index');
            Route::post('revisions/{revision}/citations', [EditorialCitationController::class, 'store'])->name('revisions.citations.store');
            Route::delete('citations/{citation}', [EditorialCitationController::class, 'destroy'])->name('citations.destroy');

            Route::get('rights-assessments', [SourceRightsReviewController::class, 'index'])->name('rights-assessments.index');
            Route::post('rights-assessments', [SourceRightsReviewController::class, 'store'])->name('rights-assessments.store');
            Route::get('rights-assessments/{assessment}', [SourceRightsReviewController::class, 'show'])->name('rights-assessments.show');

            Route::get('spoiler-boundaries', [SpoilerBoundaryController::class, 'index'])->name('spoiler-boundaries.index');
            Route::post('spoiler-boundaries', [SpoilerBoundaryController::class, 'store'])->name('spoiler-boundaries.store');
            Route::patch('spoiler-boundaries/{boundary}', [SpoilerBoundaryController::class, 'update'])->name('spoiler-boundaries.update');
        });
    });
});
