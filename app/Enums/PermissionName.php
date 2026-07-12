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

    /**
     * Get the human-readable permission label.
     */
    public function label(): string
    {
        return str($this->value)->replace(['.', '-'], ' ')->title()->toString();
    }
}
