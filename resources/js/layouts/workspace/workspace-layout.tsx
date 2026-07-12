import type { ReactNode } from 'react';
import { BrandWordmark } from '@/components/brand/brand-wordmark';
import { PageContainer } from '@/components/shell/page-frame';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

export type WorkspaceKind = 'contributor' | 'moderator' | 'administrator';
const workspaceLabels: Record<WorkspaceKind, string> = {
    contributor: 'Contributor workspace',
    moderator: 'Moderation workspace',
    administrator: 'Administration workspace',
};
export default function WorkspaceLayout({
    kind,
    children,
    navigation,
    context,
}: {
    kind: WorkspaceKind;
    children: ReactNode;
    navigation?: ReactNode;
    context?: ReactNode;
}) {
    return (
        <div
            className={cn(
                'min-h-svh bg-(--background-workspace)',
                kind === 'administrator' && 'text-sm',
            )}
        >
            <a href="#workspace-main" className="skip-link">
                Skip to workspace content
            </a>
            <header className="border-b bg-background-elevated">
                <div className="flex h-14 items-center gap-4 px-4 lg:px-6">
                    <BrandWordmark compact />
                    <Badge variant="outline">{workspaceLabels[kind]}</Badge>
                </div>
            </header>
            <div className="grid lg:grid-cols-[16rem_minmax(0,1fr)]">
                {navigation && (
                    <aside
                        aria-label={`${workspaceLabels[kind]} navigation`}
                        className="border-r bg-surface-primary p-4"
                    >
                        {navigation}
                    </aside>
                )}
                <main id="workspace-main" tabIndex={-1}>
                    <PageContainer className="max-w-(--content-wide)">
                        {children}
                    </PageContainer>
                </main>
                {context}
            </div>
        </div>
    );
}
