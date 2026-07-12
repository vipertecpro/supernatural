import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { MobileBottomNav } from '@/components/navigation/mobile-bottom-nav';
import { OfflineBanner } from '@/components/states/offline-banner';
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar';
import { fanNavigation } from '@/lib/shell/navigation';
import type { BreadcrumbItem } from '@/types';

export default function FanLayout({
    children,
    breadcrumbs = [],
}: {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
}) {
    const { sidebarOpen } = usePage().props;

    return (
        <SidebarProvider defaultOpen={sidebarOpen}>
            <a href="#main-content" className="skip-link">
                Skip to content
            </a>
            <AppSidebar />
            <SidebarInset className="min-w-0">
                <OfflineBanner />
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                <main
                    id="main-content"
                    tabIndex={-1}
                    className="min-h-[calc(100svh-var(--shell-header-height))] pb-[calc(var(--shell-bottom-nav-height)+env(safe-area-inset-bottom))] md:pb-0"
                >
                    {children}
                </main>
            </SidebarInset>
            <MobileBottomNav items={fanNavigation} />
        </SidebarProvider>
    );
}
