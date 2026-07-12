import { LoaderCircle, RefreshCw } from 'lucide-react';
import { Skeleton } from '@/components/ui/skeleton';
import { cn } from '@/lib/utils';

export function PageSkeleton() {
    return (
        <div
            aria-label="Loading page"
            role="status"
            className="flex flex-col gap-6"
        >
            <Skeleton className="h-9 w-2/3" />
            <Skeleton className="h-5 w-full max-w-2xl" />
            <div className="grid gap-4 md:grid-cols-3">
                {Array.from({ length: 3 }).map((_, i) => (
                    <Skeleton key={i} className="h-40" />
                ))}
            </div>
            <span className="sr-only">Loading</span>
        </div>
    );
}
export function CardSkeleton() {
    return (
        <div
            aria-label="Loading card"
            role="status"
            className="flex flex-col gap-3 rounded-lg border p-5"
        >
            <Skeleton className="h-5 w-1/2" />
            <Skeleton className="h-4 w-full" />
            <Skeleton className="h-4 w-4/5" />
        </div>
    );
}
export function TableSkeleton({ rows = 4 }: { rows?: number }) {
    return (
        <div
            aria-label="Loading table"
            role="status"
            className="flex flex-col gap-2"
        >
            <Skeleton className="h-10 w-full" />
            {Array.from({ length: rows }).map((_, i) => (
                <Skeleton key={i} className="h-12 w-full" />
            ))}
        </div>
    );
}
export function InlineLoading({ label = 'Loading' }: { label?: string }) {
    return (
        <span
            role="status"
            className="inline-flex items-center gap-2 text-sm text-foreground-muted"
        >
            <LoaderCircle
                className="size-4 animate-spin motion-reduce:animate-none"
                aria-hidden="true"
            />
            {label}
        </span>
    );
}
export function BackgroundRefreshIndicator({
    active,
    className,
}: {
    active: boolean;
    className?: string;
}) {
    if (!active) {
        return null;
    }

    return (
        <span
            role="status"
            aria-live="polite"
            className={cn(
                'text-caption inline-flex items-center gap-2 text-foreground-muted',
                className,
            )}
        >
            <RefreshCw className="size-3.5 animate-spin motion-reduce:animate-none" />
            Refreshing
        </span>
    );
}
