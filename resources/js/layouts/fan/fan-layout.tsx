import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { MobileBottomNav } from '@/components/navigation/mobile-bottom-nav';
import { OfflineBanner } from '@/components/states/offline-banner';
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar';
import { PublicImmersiveBackdrop } from '@/features/experience/public-immersive-backdrop';
import { PublicPageChoreography } from '@/features/experience/public-page-choreography';
import { fanNavigation } from '@/lib/shell/navigation';
import type { BreadcrumbItem } from '@/types';

export default function FanLayout({
    children,
    breadcrumbs = [],
}: {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
}) {
    const page = usePage();
    const { sidebarOpen } = page.props;

    return (
        <SidebarProvider defaultOpen={sidebarOpen}>
            <PublicImmersiveBackdrop url={page.url} />
            <PublicPageChoreography />
            <a href="#main-content" className="skip-link">
                Skip to content
            </a>
            <AppSidebar />
            <SidebarInset className="immersive-app-inset min-w-0">
                <OfflineBanner />
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                <main
                    id="main-content"
                    tabIndex={-1}
                    className="immersive-app-main min-h-[calc(100svh-var(--shell-header-height))] pb-[calc(var(--shell-bottom-nav-height)+env(safe-area-inset-bottom))] md:pb-0"
                >
                    {children}
                </main>
            </SidebarInset>
            <MobileBottomNav items={fanNavigation} />
        </SidebarProvider>
    );
}
