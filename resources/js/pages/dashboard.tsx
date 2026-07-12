import { Head } from '@inertiajs/react';
import { Archive, LockKeyhole, ShieldCheck } from 'lucide-react';
import {
    PageContainer,
    PageHeader,
    Section,
} from '@/components/shell/page-frame';
import { SpoilerRedaction } from '@/components/spoiler/spoiler-states';
import { EmptyState, RestrictedState } from '@/components/states/state-panel';
import { StatusBadge } from '@/components/status/status-badges';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';

export default function Dashboard() {
    return (
        <>
            <Head title="Home" />
            <PageContainer>
                <PageHeader
                    title="Your Archive"
                    description="The fan application shell is ready. Personal product areas remain intentionally unimplemented until their approved phases."
                    breadcrumbs={[{ title: 'Home', href: dashboard() }]}
                    badge={<StatusBadge status="draft" />}
                    metadata="PRIVATE TO YOU / FOUNDATION PREVIEW"
                />
                <div className="mt-8 flex flex-col gap-10">
                    <Section
                        title="Shell foundation"
                        description="Responsive navigation, semantic surfaces, and reusable states are active."
                    >
                        <div className="grid gap-4 md:grid-cols-3">
                            <Card>
                                <CardHeader>
                                    <Archive className="size-6 text-foreground-evidence" />
                                    <CardTitle>Practical navigation</CardTitle>
                                    <CardDescription>
                                        Desktop sidebar, tablet collapse, and
                                        mobile bottom navigation use only routes
                                        that exist.
                                    </CardDescription>
                                </CardHeader>
                            </Card>
                            <Card>
                                <CardHeader>
                                    <ShieldCheck className="size-6 text-success" />
                                    <CardTitle>Privacy-conscious</CardTitle>
                                    <CardDescription>
                                        No Journey, Community, or notification
                                        data is fabricated for this preview.
                                    </CardDescription>
                                </CardHeader>
                            </Card>
                            <Card>
                                <CardHeader>
                                    <LockKeyhole className="size-6 text-moderation" />
                                    <CardTitle>Explicit contexts</CardTitle>
                                    <CardDescription>
                                        Operational workspaces remain absent
                                        until their page routes exist.
                                    </CardDescription>
                                </CardHeader>
                            </Card>
                        </div>
                    </Section>
                    <Section
                        title="Reusable application states"
                        description="Examples verify that unfinished and withheld experiences remain honest."
                    >
                        <div className="grid gap-4 xl:grid-cols-3">
                            <EmptyState
                                title="Nothing to show yet"
                                description="Future domain screens will supply an eligible next action."
                            />
                            <RestrictedState kind="private" />
                            <SpoilerRedaction
                                severity="moderate"
                                boundary="your current viewing progress"
                            />
                        </div>
                    </Section>
                </div>
            </PageContainer>
        </>
    );
}
