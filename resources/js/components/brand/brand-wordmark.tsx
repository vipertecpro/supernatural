import { BrandMark } from '@/components/brand/brand-mark';
import { cn } from '@/lib/utils';

export function BrandWordmark({
    compact = false,
    className,
}: {
    compact?: boolean;
    className?: string;
}) {
    return (
        <span className={cn('inline-flex items-center gap-2.5', className)}>
            <BrandMark decorative className="size-8" />
            {!compact && (
                <span className="grid text-left leading-none">
                    <span className="font-editorial text-lg font-semibold tracking-tight">
                        The Archive
                    </span>
                    <span className="text-metadata text-foreground-muted">
                        working codename
                    </span>
                </span>
            )}
        </span>
    );
}
