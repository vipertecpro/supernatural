<?php

namespace App\Enums;

enum UserMuteScope: string
{
    case All = 'all';
    case CommunityContent = 'community_content';
    case Mentions = 'mentions';
    case BunkerInvitations = 'bunker_invitations';
}
