import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import { PageContainer, PageHeader } from '@/components/shell/page-frame';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn, toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

const items: NavItem[] = [
    { title: 'Profile', href: editProfile() },
    { title: 'Security', href: editSecurity() },
    { title: 'Appearance', href: editAppearance() },
];
export default function SettingsLayout({ children }: PropsWithChildren) {
    const { isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <PageContainer>
            <PageHeader
                title="Settings"
                description="Manage account details, security, and your local appearance preference."
            />
            <div className="mt-8 grid gap-8 lg:grid-cols-[12rem_minmax(0,1fr)]">
                <aside>
                    <nav
                        aria-label="Settings"
                        className="flex gap-2 overflow-x-auto lg:flex-col"
                    >
                        {items.map((item) => (
                            <Button
                                key={toUrl(item.href)}
                                size="sm"
                                variant="ghost"
                                asChild
                                className={cn(
                                    'shrink-0 justify-start',
                                    isCurrentOrParentUrl(item.href) &&
                                        'bg-surface-selected',
                                )}
                            >
                                <Link
                                    href={item.href}
                                    aria-current={
                                        isCurrentOrParentUrl(item.href)
                                            ? 'page'
                                            : undefined
                                    }
                                >
                                    {item.title}
                                </Link>
                            </Button>
                        ))}
                    </nav>
                </aside>
                <Separator className="lg:hidden" />
                <section className="max-w-2xl min-w-0 rounded-xl border bg-surface-primary p-5 shadow-surface sm:p-7">
                    {children}
                </section>
            </div>
        </PageContainer>
    );
}
