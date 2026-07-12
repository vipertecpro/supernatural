import type { ReactNode } from 'react';
import { PublicPageChoreography } from '@/features/experience/public-page-choreography';
import PublicMarketingLayout from '@/layouts/public/public-marketing-layout';
import { cn } from '@/lib/utils';

export default function PublicContentLayout({
    children,
    context,
    sourceDrawer,
    related,
    wide = false,
}: {
    children: ReactNode;
    context?: ReactNode;
    sourceDrawer?: ReactNode;
    related?: ReactNode;
    wide?: boolean;
}) {
    return (
        <PublicMarketingLayout>
            <PublicPageChoreography />
            <div
                className={cn(
                    'public-content-shell mx-auto grid w-full gap-8 px-4 pb-20 sm:px-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:pb-32',
                    wide
                        ? 'max-w-(--content-wide)'
                        : 'max-w-(--content-default)',
                )}
            >
                <article className="public-prose text-body max-w-none min-w-0">
                    {children}
                </article>
                {context && (
                    <aside
                        aria-label="Page context"
                        className="order-first lg:order-none"
                    >
                        <div className="rounded-lg border bg-surface-primary p-5 lg:sticky lg:top-20">
                            {context}
                        </div>
                    </aside>
                )}
                {sourceDrawer && (
                    <section aria-label="Sources" className="lg:col-span-2">
                        {sourceDrawer}
                    </section>
                )}
                {related && (
                    <section
                        aria-label="Related content"
                        className="lg:col-span-2"
                    >
                        {related}
                    </section>
                )}
            </div>
        </PublicMarketingLayout>
    );
}
