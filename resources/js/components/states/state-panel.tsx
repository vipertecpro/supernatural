import {
    AlertCircle,
    Archive,
    FileWarning,
    LockKeyhole,
    RefreshCw,
    ShieldAlert,
    WifiOff,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Empty,
    EmptyContent,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
} from '@/components/ui/empty';

export function EmptyState({
    icon: Icon = Archive,
    title,
    description,
    primaryAction,
    secondaryAction,
}: {
    icon?: LucideIcon;
    title: string;
    description: string;
    primaryAction?: ReactNode;
    secondaryAction?: ReactNode;
}) {
    return (
        <Empty className="border border-dashed border-border-strong/60 bg-surface-secondary/40">
            <EmptyHeader>
                <EmptyMedia variant="icon">
                    <Icon />
                </EmptyMedia>
                <EmptyTitle>{title}</EmptyTitle>
                <EmptyDescription>{description}</EmptyDescription>
            </EmptyHeader>
            {(primaryAction || secondaryAction) && (
                <EmptyContent className="flex-row flex-wrap justify-center">
                    {primaryAction}
                    {secondaryAction}
                </EmptyContent>
            )}
        </Empty>
    );
}

type ErrorKind =
    | 'request'
    | 'validation'
    | 'authorization'
    | 'rate-limit'
    | 'server'
    | 'unavailable';
const errorCopy: Record<
    ErrorKind,
    { title: string; description: string; icon: LucideIcon }
> = {
    request: {
        title: 'The request could not be completed',
        description: 'Check your connection and try again.',
        icon: AlertCircle,
    },
    validation: {
        title: 'Review the highlighted fields',
        description: 'Some information needs attention before you continue.',
        icon: FileWarning,
    },
    authorization: {
        title: 'You do not have access',
        description: 'Return to a place available to your account.',
        icon: LockKeyhole,
    },
    'rate-limit': {
        title: 'Please wait before trying again',
        description: 'Too many requests were made in a short period.',
        icon: ShieldAlert,
    },
    server: {
        title: 'The Archive is having trouble',
        description:
            'Try again. If the problem continues, keep the request reference.',
        icon: AlertCircle,
    },
    unavailable: {
        title: 'Service temporarily unavailable',
        description: 'This area cannot be reached right now.',
        icon: WifiOff,
    },
};
export function ErrorState({
    kind = 'request',
    requestId,
    onRetry,
}: {
    kind?: ErrorKind;
    requestId?: string;
    onRetry?: () => void;
}) {
    const state = errorCopy[kind];
    const Icon = state.icon;

    return (
        <Alert variant="destructive" role="alert">
            <Icon />
            <AlertTitle>{state.title}</AlertTitle>
            <AlertDescription>
                <p>{state.description}</p>
                {requestId && (
                    <p className="mt-2 font-evidence text-xs">
                        Request reference: {requestId}
                    </p>
                )}
                {onRetry && (
                    <Button
                        className="mt-4"
                        size="sm"
                        variant="outline"
                        onClick={onRetry}
                    >
                        <RefreshCw data-icon="inline-start" />
                        Retry
                    </Button>
                )}
            </AlertDescription>
        </Alert>
    );
}

type RestrictionKind =
    | 'permission'
    | 'account'
    | 'content'
    | 'rights'
    | 'private'
    | 'archived'
    | 'removed';
const restrictionCopy: Record<RestrictionKind, string> = {
    permission: 'Your account is not authorized for this area.',
    account: 'This account is currently restricted.',
    content: 'This content is unavailable.',
    rights: 'This resource is unavailable because of a rights restriction.',
    private: 'This resource is private.',
    archived: 'This resource has been archived.',
    removed: 'This resource has been removed.',
};
export function RestrictedState({
    kind = 'permission',
    action,
}: {
    kind?: RestrictionKind;
    action?: ReactNode;
}) {
    return (
        <EmptyState
            icon={kind === 'archived' ? Archive : LockKeyhole}
            title={
                kind === 'private' ? 'Private resource' : 'Access unavailable'
            }
            description={restrictionCopy[kind]}
            primaryAction={action}
        />
    );
}

export function ConflictState({
    onReload,
    compareSlot,
}: {
    onReload?: () => void;
    compareSlot?: ReactNode;
}) {
    return (
        <Alert className="border-warning bg-surface-spoiler">
            <FileWarning />
            <AlertTitle>Newer changes are available</AlertTitle>
            <AlertDescription>
                <p>
                    Your unsaved changes have not been overwritten. Reload the
                    latest version before retrying.
                </p>
                {compareSlot}
                {onReload && (
                    <Button className="mt-4" size="sm" onClick={onReload}>
                        <RefreshCw data-icon="inline-start" />
                        Reload latest
                    </Button>
                )}
            </AlertDescription>
        </Alert>
    );
}

export function UnavailableState({
    title = 'Resource unavailable',
    description = 'It may have been removed, archived, or you may not have access.',
}: {
    title?: string;
    description?: string;
}) {
    return (
        <EmptyState
            icon={AlertCircle}
            title={title}
            description={description}
        />
    );
}
