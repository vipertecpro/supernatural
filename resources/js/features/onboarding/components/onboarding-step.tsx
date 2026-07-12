import { Link } from '@inertiajs/react';
import { ArrowLeft, ArrowRight } from 'lucide-react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';

export function OnboardingStepHeader({
    eyebrow,
    title,
    description,
}: {
    eyebrow: string;
    title: string;
    description: string;
}) {
    return (
        <header className="max-w-2xl">
            <p className="text-metadata text-foreground-evidence">{eyebrow}</p>
            <h1
                data-onboarding-heading
                tabIndex={-1}
                className="text-page-title mt-3 focus:outline-none"
            >
                {title}
            </h1>
            <p className="text-body mt-3 text-foreground-secondary">
                {description}
            </p>
        </header>
    );
}

export function OnboardingSelectionCard({
    children,
    selected = false,
    className,
}: {
    children: ReactNode;
    selected?: boolean;
    className?: string;
}) {
    return (
        <div
            className={cn(
                'rounded-xl border bg-surface-primary p-4 transition-colors',
                selected
                    ? 'border-action ring-action/20 ring-2'
                    : 'border-border-strong hover:border-foreground-muted',
                className,
            )}
        >
            {children}
        </div>
    );
}

export function OnboardingStepActions({
    backHref,
    processing,
    continueLabel = 'Continue',
    secondary,
}: {
    backHref: string | null;
    processing: boolean;
    continueLabel?: string;
    secondary?: ReactNode;
}) {
    return (
        <footer className="sticky bottom-0 -mx-4 mt-8 border-t border-border bg-background/95 px-4 py-4 backdrop-blur sm:static sm:mx-0 sm:flex sm:items-center sm:justify-between sm:border-0 sm:bg-transparent sm:px-0 sm:py-0 sm:backdrop-blur-none">
            <div className="flex items-center gap-2">
                {backHref && (
                    <Button variant="ghost" asChild>
                        <Link href={backHref}>
                            <ArrowLeft data-icon="inline-start" />
                            Back
                        </Link>
                    </Button>
                )}
                {secondary}
            </div>
            <Button
                type="submit"
                disabled={processing}
                className="w-full sm:w-auto"
            >
                {processing ? (
                    <Spinner />
                ) : (
                    <ArrowRight data-icon="inline-end" />
                )}
                {processing ? 'Saving…' : continueLabel}
            </Button>
        </footer>
    );
}
