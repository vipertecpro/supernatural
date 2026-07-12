<?php

namespace App\Enums;

enum RestrictionScope: string
{
    case PlatformAccess = 'platform_access';
    case CatalogContribution = 'catalog_contribution';
    case LoreContribution = 'lore_contribution';
    case EditorialSubmission = 'editorial_submission';
    case MediaSubmission = 'media_submission';
    case MediaAttachment = 'media_attachment';
    case ReportSubmission = 'report_submission';
}
