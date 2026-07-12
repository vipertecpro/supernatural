import { Eye, EyeOff, ShieldAlert, TriangleAlert } from 'lucide-react';
import type { ReactNode } from 'react';
import { EmptyState } from '@/components/states/state-panel';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export type SpoilerSeverity =
    'safe' | 'minor' | 'moderate' | 'major' | 'finale';
export type SpoilerOutcome = 'visible' | 'warning' | 'redacted' | 'hidden';
const labels: Record<SpoilerSeverity, string> = {
    safe: 'Spoiler safe',
    minor: 'Minor spoilers',
    moderate: 'Moderate spoilers',
    major: 'Major spoilers',
    finale: 'Finale spoilers',
};
const styles: Record<SpoilerSeverity, string> = {
    safe: 'border-success text-success',
    minor: 'border-spoiler-minor text-spoiler-minor',
    moderate: 'border-spoiler-moderate text-spoiler-moderate',
    major: 'border-spoiler-major text-spoiler-major',
    finale: 'border-spoiler-finale text-spoiler-finale',
};
export function SpoilerBadge({ severity }: { severity: SpoilerSeverity }) {
    return (
        <Badge variant="outline" className={cn('gap-1.5', styles[severity])}>
            <ShieldAlert />
            {labels[severity]}
        </Badge>
    );
}
export function SpoilerWarning({
    severity,
    boundary,
    onReveal,
    children,
}: {
    severity: Exclude<SpoilerSeverity, 'safe'>;
    boundary?: string;
    onReveal?: () => void;
    children?: ReactNode;
}) {
    return (
        <section
            aria-label={`${labels[severity]} warning`}
            className="rounded-lg border border-warning bg-surface-spoiler p-5"
        >
            <div className="flex flex-col gap-3">
                <SpoilerBadge severity={severity} />
                <div>
                    <h3 className="text-card-title">Spoiler warning</h3>
                    <p className="text-body-sm mt-1 text-foreground-secondary">
                        {boundary
                            ? `Details may reveal events beyond ${boundary}.`
                            : 'Details may reveal story events.'}
                    </p>
                </div>
                {onReveal && (
                    <Button
                        className="self-start"
                        size="sm"
                        variant="outline"
                        onClick={onReveal}
                    >
                        <Eye data-icon="inline-start" />
                        Reveal for this session
                    </Button>
                )}
                {children}
            </div>
        </section>
    );
}
export function SpoilerRedaction({
    severity,
    boundary,
}: {
    severity: Exclude<SpoilerSeverity, 'safe'>;
    boundary?: string;
}) {
    return (
        <div
            role="status"
            className="rounded-lg border border-warning bg-surface-spoiler p-5"
        >
            <SpoilerBadge severity={severity} />
            <p className="mt-3 font-medium">
                Spoiler-sensitive details withheld
            </p>
            <p className="mt-1 text-sm text-foreground-muted">
                {boundary
                    ? `Your current boundary is ${boundary}.`
                    : 'Update your spoiler settings to change future responses.'}
            </p>
        </div>
    );
}
export function SpoilerUnavailable() {
    return (
        <EmptyState
            icon={EyeOff}
            title="Spoiler-sensitive content unavailable"
            description="The server did not return the withheld text."
        />
    );
}
export function SpoilerBoundaryLabel({ children }: { children: ReactNode }) {
    return (
        <span className="text-metadata inline-flex items-center gap-2 text-foreground-evidence">
            <TriangleAlert className="size-4" />
            Boundary: {children}
        </span>
    );
}
export function SpoilerState({
    outcome,
    severity,
    children,
}: {
    outcome: SpoilerOutcome;
    severity: SpoilerSeverity;
    children?: ReactNode;
}) {
    if (outcome === 'hidden') {
        return <SpoilerUnavailable />;
    }

    if (outcome === 'redacted') {
        return (
            <SpoilerRedaction
                severity={severity === 'safe' ? 'minor' : severity}
            />
        );
    }

    if (outcome === 'warning') {
        return (
            <SpoilerWarning severity={severity === 'safe' ? 'minor' : severity}>
                {children}
            </SpoilerWarning>
        );
    }

    return <>{children}</>;
}
