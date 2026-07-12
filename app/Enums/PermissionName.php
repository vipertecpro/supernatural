<?php

namespace App\Enums;

enum PermissionName: string
{
    case DashboardAccess = 'dashboard.access';
    case ContentContribute = 'content.contribute';
    case ContentReview = 'content.review';
    case CommunityModerate = 'community.moderate';
    case UsersModerate = 'users.moderate';
    case UsersManage = 'users.manage';
    case SettingsManage = 'settings.manage';
    case AuditLogsView = 'audit-logs.view';
    case AdministrationAccess = 'administration.access';
    case CatalogViewDrafts = 'catalog.view-drafts';
    case CatalogCreate = 'catalog.create';
    case CatalogUpdate = 'catalog.update';
    case CatalogPublish = 'catalog.publish';
    case CatalogArchive = 'catalog.archive';
    case CatalogDelete = 'catalog.delete';
    case EditorialRevisionsCreate = 'editorial.revisions.create';
    case EditorialRevisionsViewOwn = 'editorial.revisions.view-own';
    case EditorialRevisionsViewAll = 'editorial.revisions.view-all';
    case EditorialRevisionsReview = 'editorial.revisions.review';
    case EditorialRevisionsAssign = 'editorial.revisions.assign';
    case EditorialRevisionsApprove = 'editorial.revisions.approve';
    case EditorialRevisionsApply = 'editorial.revisions.apply';
    case EditorialCitationsManage = 'editorial.citations.manage';
    case EditorialRightsAssess = 'editorial.rights.assess';
    case EditorialRightsReview = 'editorial.rights.review';
    case EditorialSpoilersClassify = 'editorial.spoilers.classify';
    case EditorialSpoilersReview = 'editorial.spoilers.review';
    case EditorialSpoilersBypass = 'editorial.spoilers.bypass';
    case LoreViewDrafts = 'lore.view-drafts';
    case LoreCreate = 'lore.create';
    case LoreUpdate = 'lore.update';
    case LorePublish = 'lore.publish';
    case LoreArchive = 'lore.archive';
    case LoreDelete = 'lore.delete';
    case LoreRelationshipsCreate = 'lore.relationships.create';
    case LoreRelationshipsReview = 'lore.relationships.review';
    case LoreRelationshipsPublish = 'lore.relationships.publish';
    case LoreTimelinesCreate = 'lore.timelines.create';
    case LoreTimelinesUpdate = 'lore.timelines.update';
    case LoreTimelinesPublish = 'lore.timelines.publish';
    case MediaViewDrafts = 'media.view-drafts';
    case MediaCreate = 'media.create';
    case MediaUpdateOwnDrafts = 'media.update-own-drafts';
    case MediaAttach = 'media.attach';
    case MediaReview = 'media.review';
    case MediaPublish = 'media.publish';
    case MediaArchive = 'media.archive';
    case MediaRightsReview = 'media.rights-review';
    case MediaModerate = 'media.moderate';
    case SearchRebuild = 'search.rebuild';
    case SearchInspectProjections = 'search.inspect-projections';
    case SearchManage = 'search.manage';
    case JourneyViewingOrdersCreate = 'journey.viewing-orders.create';
    case JourneyViewingOrdersUpdate = 'journey.viewing-orders.update';
    case JourneyViewingOrdersPublish = 'journey.viewing-orders.publish';
    case JourneyViewingOrdersArchive = 'journey.viewing-orders.archive';
    case ModerationReportsView = 'moderation.reports.view';
    case ModerationReportsTriage = 'moderation.reports.triage';
    case ModerationCasesCreate = 'moderation.cases.create';
    case ModerationCasesView = 'moderation.cases.view';
    case ModerationCasesAssign = 'moderation.cases.assign';
    case ModerationCasesInvestigate = 'moderation.cases.investigate';
    case ModerationCasesReopen = 'moderation.cases.reopen';
    case ModerationActionsApply = 'moderation.actions.apply';
    case ModerationUserRestrictionsApply = 'moderation.restrictions.user';
    case ModerationContentRestrictionsApply = 'moderation.restrictions.content';
    case ModerationPermanentRestrictionsApply = 'moderation.restrictions.permanent';
    case ModerationAppealsReview = 'moderation.appeals.review';
    case NotificationTypesManage = 'notifications.types.manage';
    case NotificationDeliveriesInspect = 'notifications.inspect-deliveries';
    case NotificationDeliveriesRetry = 'notifications.retry-deliveries';

    /**
     * Get the human-readable permission label.
     */
    public function label(): string
    {
        return str($this->value)->replace(['.', '-'], ' ')->title()->toString();
    }
}
