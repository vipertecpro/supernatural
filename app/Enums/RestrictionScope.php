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
    case CommunityContentCreation = 'community_content_creation';
    case BunkerCreation = 'bunker_creation';
    case BunkerMembershipRequest = 'bunker_membership_request';
    case CommunityCommenting = 'community_commenting';
    case CommunityReacting = 'community_reacting';
    case CommunityMentioning = 'community_mentioning';
    case CommunityPollVoting = 'community_poll_voting';
}
