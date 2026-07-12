import {
    Archive,
    CheckCircle2,
    CircleDashed,
    Crown,
    EyeOff,
    LockKeyhole,
    Shield,
    UserRound,
    Users,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

export type LifecycleStatus =
    | 'draft'
    | 'submitted'
    | 'under-review'
    | 'changes-requested'
    | 'approved'
    | 'published'
    | 'archived'
    | 'restricted'
    | 'removed'
    | 'suspended';
const lifecycle: Record<
    LifecycleStatus,
    { label: string; icon: LucideIcon; className: string }
> = {
    draft: { label: 'Draft', icon: CircleDashed, className: '' },
    submitted: {
        label: 'Submitted',
        icon: CircleDashed,
        className: 'border-information text-information',
    },
    'under-review': {
        label: 'Under review',
        icon: Shield,
        className: 'border-editorial text-editorial',
    },
    'changes-requested': {
        label: 'Changes requested',
        icon: CircleDashed,
        className: 'border-warning text-warning',
    },
    approved: {
        label: 'Approved',
        icon: CheckCircle2,
        className: 'border-success text-success',
    },
    published: {
        label: 'Published',
        icon: CheckCircle2,
        className: 'border-success text-success',
    },
    archived: {
        label: 'Archived',
        icon: Archive,
        className: 'border-archived text-archived',
    },
    restricted: {
        label: 'Restricted',
        icon: LockKeyhole,
        className: 'border-restricted text-restricted',
    },
    removed: {
        label: 'Removed',
        icon: EyeOff,
        className: 'border-danger text-danger',
    },
    suspended: {
        label: 'Suspended',
        icon: Shield,
        className: 'border-danger text-danger',
    },
};
export function StatusBadge({ status }: { status: LifecycleStatus }) {
    const item = lifecycle[status];
    const Icon = item.icon;

    return (
        <Badge
            variant="outline"
            className={cn('gap-1.5', item.className)}
            aria-label={`Status: ${item.label}`}
        >
            <Icon />
            {item.label}
        </Badge>
    );
}

export type Authority =
    | 'owner'
    | 'administrator'
    | 'platform-moderator'
    | 'bunker-moderator'
    | 'member'
    | 'contributor';
const authority: Record<
    Authority,
    { label: string; icon: LucideIcon; className: string }
> = {
    owner: {
        label: 'Owner',
        icon: Crown,
        className: 'border-editorial text-editorial',
    },
    administrator: {
        label: 'Administrator',
        icon: Shield,
        className: 'border-moderation text-moderation',
    },
    'platform-moderator': {
        label: 'Platform moderator',
        icon: Shield,
        className: 'border-moderation text-moderation',
    },
    'bunker-moderator': {
        label: 'Bunker moderator',
        icon: Users,
        className: 'border-information text-information',
    },
    member: { label: 'Member', icon: UserRound, className: '' },
    contributor: {
        label: 'Contributor',
        icon: CircleDashed,
        className: 'border-editorial text-editorial',
    },
};
export function AuthorityBadge({ authority: value }: { authority: Authority }) {
    const item = authority[value];
    const Icon = item.icon;

    return (
        <Badge
            variant="outline"
            className={cn('gap-1.5', item.className)}
            aria-label={`Authority: ${item.label}`}
        >
            <Icon />
            {item.label}
        </Badge>
    );
}
export function VisibilityBadge({
    visibility,
}: {
    visibility: 'private' | 'invite-only' | 'public';
}) {
    const label =
        visibility === 'invite-only'
            ? 'Invite only'
            : visibility[0].toUpperCase() + visibility.slice(1);

    return (
        <Badge variant="secondary">
            <LockKeyhole />
            {label}
        </Badge>
    );
}
