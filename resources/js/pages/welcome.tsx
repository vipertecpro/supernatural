import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowRight, BookOpen, Layers3, ShieldCheck } from 'lucide-react';
import { BrandMark } from '@/components/brand/brand-mark';
import { PageContainer, Section } from '@/components/shell/page-frame';
import { StatusBadge } from '@/components/status/status-badges';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard, login, register } from '@/routes';

const foundations = [
    {
        icon: BookOpen,
        title: 'Evidence before certainty',
        description:
            'Editorial, source, rights, and spoiler states remain visibly distinct.',
    },
    {
        icon: Layers3,
        title: 'One system, clear contexts',
        description:
            'Public, fan, contributor, moderation, and administration surfaces share foundations without sharing assumptions.',
    },
    {
        icon: ShieldCheck,
        title: 'Privacy in the structure',
        description:
            'Private activity and protected details are not used as decorative product data.',
    },
];
export default function Welcome() {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Foundation" />
            <section className="archive-atmosphere border-b border-border-subtle">
                <PageContainer className="grid min-h-[70svh] content-center gap-10 py-16 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-center">
                    <div>
                        <StatusBadge status="draft" />
                        <p className="text-label mt-6 tracking-[0.18em] text-foreground-evidence uppercase">
                            Fandom-neutral knowledge platform
                        </p>
                        <h1 className="text-display-lg mt-4 max-w-4xl text-balance">
                            A clearer way to preserve stories, evidence, and
                            shared discovery.
                        </h1>
                        <p className="text-body-lg mt-6 max-w-2xl text-foreground-secondary">
                            The Archive is the working codename for an original
                            platform foundation now under active development.
                            This preview demonstrates the design system—not
                            unfinished product features.
                        </p>
                        <div className="mt-8 flex flex-wrap gap-3">
                            {auth.user ? (
                                <Button size="lg" asChild>
                                    <Link href={dashboard()}>
                                        Open the fan application
                                        <ArrowRight data-icon="inline-end" />
                                    </Link>
                                </Button>
                            ) : (
                                <>
                                    <Button size="lg" asChild>
                                        <Link href={register()}>
                                            Create an account
                                            <ArrowRight data-icon="inline-end" />
                                        </Link>
                                    </Button>
                                    <Button size="lg" variant="outline" asChild>
                                        <Link href={login()}>Sign in</Link>
                                    </Button>
                                </>
                            )}
                        </div>
                    </div>
                    <div className="mx-auto flex aspect-square w-full max-w-sm items-center justify-center rounded-full border border-border-strong bg-surface-primary/70 shadow-overlay">
                        <BrandMark
                            decorative
                            className="w-1/2 text-foreground"
                        />
                    </div>
                </PageContainer>
            </section>
            <PageContainer className="py-16">
                <Section
                    title="Foundation principles"
                    description="The first frontend phase establishes reusable structure without pretending the domain screens exist."
                >
                    <div className="grid gap-4 md:grid-cols-3">
                        {foundations.map(
                            ({ icon: Icon, title, description }) => (
                                <Card key={title}>
                                    <CardHeader>
                                        <Icon
                                            className="size-6 text-foreground-evidence"
                                            aria-hidden="true"
                                        />
                                        <CardTitle>{title}</CardTitle>
                                        <CardDescription>
                                            {description}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <p className="text-metadata text-foreground-muted">
                                            FOUNDATION / PROMPT 13
                                        </p>
                                    </CardContent>
                                </Card>
                            ),
                        )}
                    </div>
                </Section>
            </PageContainer>
        </>
    );
}
