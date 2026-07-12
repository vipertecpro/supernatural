import type { HTMLAttributes, ReactNode } from 'react';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { cn } from '@/lib/utils';
import type { BreadcrumbItem } from '@/types';

export function PageContainer({
    className,
    ...props
}: HTMLAttributes<HTMLDivElement>) {
    return (
        <div
            className={cn(
                'mx-auto w-full max-w-(--content-default) px-4 py-6 sm:px-6 lg:px-8 lg:py-8',
                className,
            )}
            {...props}
        />
    );
}

export function PageHeader({
    title,
    description,
    breadcrumbs = [],
    badge,
    actions,
    metadata,
}: {
    title: string;
    description?: string;
    breadcrumbs?: BreadcrumbItem[];
    badge?: ReactNode;
    actions?: ReactNode;
    metadata?: ReactNode;
}) {
    return (
        <header className="flex flex-col gap-5 border-b border-border-subtle pb-6">
            {breadcrumbs.length > 0 && (
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            )}
            <div className="flex flex-col justify-between gap-4 md:flex-row md:items-end">
                <div className="max-w-3xl min-w-0">
                    <div className="flex flex-wrap items-center gap-3">
                        <h1 className="text-page-title text-balance">
                            {title}
                        </h1>
                        {badge}
                    </div>
                    {description && (
                        <p className="text-body mt-2 text-pretty text-foreground-secondary">
                            {description}
                        </p>
                    )}
                    {metadata && (
                        <div className="text-metadata mt-3 text-foreground-evidence">
                            {metadata}
                        </div>
                    )}
                </div>
                {actions && (
                    <div className="flex shrink-0 flex-wrap items-center gap-2">
                        {actions}
                    </div>
                )}
            </div>
        </header>
    );
}

export function Section({
    title,
    description,
    actions,
    children,
    className,
}: {
    title?: string;
    description?: string;
    actions?: ReactNode;
    children: ReactNode;
    className?: string;
}) {
    return (
        <section className={cn('flex flex-col gap-5', className)}>
            {(title || description || actions) && (
                <header className="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                    <div>
                        {title && (
                            <h2 className="text-section-title">{title}</h2>
                        )}
                        {description && (
                            <p className="text-body-sm mt-1 text-foreground-muted">
                                {description}
                            </p>
                        )}
                    </div>
                    {actions}
                </header>
            )}
            {children}
        </section>
    );
}

export function ContentGrid({
    className,
    ...props
}: HTMLAttributes<HTMLDivElement>) {
    return (
        <div
            className={cn(
                'grid gap-4 sm:grid-cols-2 xl:grid-cols-3',
                className,
            )}
            {...props}
        />
    );
}
