import { Link, usePage } from '@inertiajs/react';
import { Check, LockKeyhole } from 'lucide-react';
import { useEffect } from 'react';
import type { ReactNode } from 'react';
import { BrandWordmark } from '@/components/brand/brand-wordmark';
import { AppearanceMenu } from '@/components/navigation/appearance-menu';
import { Button } from '@/components/ui/button';
import type { OnboardingPageProps } from '@/features/onboarding/types';
import { cn } from '@/lib/utils';
import { logout } from '@/routes';
import { edit as appearance } from '@/routes/appearance';

export default function OnboardingLayout({
    children,
}: {
    children: ReactNode;
}) {
    const { onboarding } = usePage<OnboardingPageProps>().props;
    const currentIndex = onboarding.steps.findIndex(
        (step) => step.key === onboarding.currentStep,
    );

    useEffect(() => {
        document
            .querySelector<HTMLElement>('[data-onboarding-heading]')
            ?.focus();
    }, [onboarding.currentStep]);

    return (
        <div className="archive-atmosphere min-h-svh bg-(--background-public)">
            <a href="#onboarding-content" className="skip-link">
                Skip to setup
            </a>
            <header className="border-b border-border bg-surface-primary/90 backdrop-blur">
                <div className="mx-auto flex min-h-16 max-w-(--content-wide) items-center justify-between gap-4 px-4 sm:px-6">
                    <BrandWordmark />
                    <div className="flex items-center gap-1">
                        <AppearanceMenu />
                        <Button
                            variant="ghost"
                            size="sm"
                            className="hidden sm:inline-flex"
                            asChild
                        >
                            <Link href={appearance()}>Appearance</Link>
                        </Button>
                        <Button variant="ghost" size="sm" asChild>
                            <Link href={logout()} method="post" as="button">
                                Sign out
                            </Link>
                        </Button>
                    </div>
                </div>
            </header>

            <div className="mx-auto grid max-w-(--content-wide) gap-8 px-4 py-6 sm:px-6 lg:grid-cols-[15rem_minmax(0,1fr)] lg:py-10">
                <aside
                    aria-label="Onboarding progress"
                    className="lg:sticky lg:top-6 lg:self-start"
                >
                    <div className="rounded-xl border border-border-strong bg-surface-primary p-4">
                        <div className="flex items-center gap-2 text-sm font-medium">
                            <LockKeyhole className="size-4 text-success" />
                            Private setup
                        </div>
                        <p className="mt-2 text-sm text-foreground-muted">
                            Step {currentIndex + 1} of {onboarding.steps.length}
                        </p>
                        <ol className="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-1">
                            {onboarding.steps.map((step, index) => {
                                const content = (
                                    <span className="flex min-h-11 items-center gap-3 rounded-lg px-3 py-2 text-sm">
                                        <span
                                            className={cn(
                                                'flex size-6 shrink-0 items-center justify-center rounded-full border text-xs',
                                                step.status === 'current' &&
                                                    'border-action bg-action text-action-foreground',
                                                step.status === 'complete' &&
                                                    'border-success text-success',
                                            )}
                                        >
                                            {step.status === 'complete' ? (
                                                <Check className="size-3.5" />
                                            ) : (
                                                index + 1
                                            )}
                                        </span>
                                        <span>{step.label}</span>
                                    </span>
                                );

                                return (
                                    <li key={step.key}>
                                        {step.href ? (
                                            <Link
                                                href={step.href}
                                                aria-current={
                                                    step.status === 'current'
                                                        ? 'step'
                                                        : undefined
                                                }
                                                className={cn(
                                                    'block rounded-lg focus-visible:ring-[3px] focus-visible:ring-ring',
                                                    step.status === 'current' &&
                                                        'bg-surface-secondary font-medium',
                                                )}
                                            >
                                                {content}
                                            </Link>
                                        ) : (
                                            <span className="block text-foreground-muted">
                                                {content}
                                            </span>
                                        )}
                                    </li>
                                );
                            })}
                        </ol>
                    </div>
                </aside>

                <main
                    id="onboarding-content"
                    className="min-w-0 rounded-xl border border-border-strong bg-surface-primary p-5 shadow-surface sm:p-8"
                >
                    <p className="sr-only" role="status" aria-live="polite">
                        Step {currentIndex + 1} of {onboarding.steps.length}:{' '}
                        {onboarding.steps[currentIndex]?.label}
                    </p>
                    {children}
                </main>
            </div>
        </div>
    );
}
