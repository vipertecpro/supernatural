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

    /**
     * Get the human-readable permission label.
     */
    public function label(): string
    {
        return str($this->value)->replace(['.', '-'], ' ')->title()->toString();
    }
}
