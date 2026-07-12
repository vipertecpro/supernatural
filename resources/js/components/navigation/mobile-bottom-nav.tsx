import { Link } from '@inertiajs/react';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { ShellNavigationItem } from '@/lib/shell/navigation';
import { cn } from '@/lib/utils';

export function MobileBottomNav({ items }: { items: ShellNavigationItem[] }) {
    const { isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <nav
            aria-label="Primary mobile navigation"
            className="fixed inset-x-0 bottom-0 border-t border-border-strong bg-background-elevated/95 pb-[env(safe-area-inset-bottom)] backdrop-blur md:hidden"
        >
            <div className="mx-auto grid max-w-md auto-cols-fr grid-flow-col">
                {items.slice(0, 5).map((item) => {
                    const Icon = item.icon;
                    const active = isCurrentOrParentUrl(item.href);

                    return (
                        <Link
                            key={item.title}
                            href={item.href}
                            aria-current={active ? 'page' : undefined}
                            className={cn(
                                'text-caption flex min-h-14 flex-col items-center justify-center gap-1 px-2 py-2 font-medium text-foreground-muted',
                                active && 'bg-surface-selected text-foreground',
                            )}
                        >
                            {Icon && (
                                <Icon className="size-5" aria-hidden="true" />
                            )}
                            <span>{item.title}</span>
                            <span className="sr-only">
                                {active ? ', current page' : ''}
                            </span>
                        </Link>
                    );
                })}
            </div>
        </nav>
    );
}
