import { usePage } from '@inertiajs/react';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { AppearanceMenu } from '@/components/navigation/appearance-menu';
import { WorkspaceSwitcher } from '@/components/navigation/workspace-switcher';
import { Separator } from '@/components/ui/separator';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    const { navigation } = usePage().props;

    return (
        <header className="flex h-(--shell-header-height) shrink-0 items-center gap-3 border-b border-sidebar-border px-4">
            <SidebarTrigger aria-label="Toggle navigation" />
            <Separator orientation="vertical" className="h-5" />
            <div className="min-w-0 flex-1">
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>
            <WorkspaceSwitcher workspaces={navigation.workspaces} />
            <AppearanceMenu />
        </header>
    );
}
