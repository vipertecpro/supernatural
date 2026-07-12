import { Form, Head, Link } from '@inertiajs/react';
import { CheckCircle2, Pencil } from 'lucide-react';
import CompleteOnboardingController from '@/actions/App/Http/Controllers/Onboarding/CompleteOnboardingController';
import { edit as editPrivacy } from '@/actions/App/Http/Controllers/Onboarding/PrivacyDefaultsController';
import { edit as editSpoilers } from '@/actions/App/Http/Controllers/Onboarding/SpoilerPreferencesController';
import { edit as editInterests } from '@/actions/App/Http/Controllers/Onboarding/UniverseInterestsController';
import { edit as editOrder } from '@/actions/App/Http/Controllers/Onboarding/ViewingOrderController';
import { edit as editProgress } from '@/actions/App/Http/Controllers/Onboarding/ViewingProgressController';
import { FormErrorSummary } from '@/components/forms/form-error-summary';
import { Button } from '@/components/ui/button';
import {
    OnboardingStepActions,
    OnboardingStepHeader,
} from '@/features/onboarding/components/onboarding-step';
import type { OnboardingPageProps } from '@/features/onboarding/types';

type Summary = {
    universes: Array<{ id: number; name: string }>;
    progress: { work: string; status: string } | null;
    spoilers: Array<{
        universe: string;
        tolerance: string;
        warnings: boolean;
    }>;
    viewingOrders: Array<{ universe: string; name: string }>;
    privacy: string[];
};

function ReviewSection({
    title,
    editHref,
    children,
}: {
    title: string;
    editHref: ReturnType<typeof editInterests>;
    children: React.ReactNode;
}) {
    return (
        <section className="rounded-xl border border-border p-5">
            <div className="flex items-start justify-between gap-3">
                <h2 className="text-card-title">{title}</h2>
                <Button variant="ghost" size="sm" asChild>
                    <Link href={editHref}>
                        <Pencil data-icon="inline-start" />
                        Edit
                    </Link>
                </Button>
            </div>
            <div className="mt-3 text-sm text-foreground-secondary">
                {children}
            </div>
        </section>
    );
}

export default function Review({
    onboarding,
    summary,
}: OnboardingPageProps & { summary: Summary }) {
    return (
        <>
            <Head title="Review onboarding" />
            <OnboardingStepHeader
                eyebrow="Step 7"
                title="Review your setup"
                description="Confirm the safe, non-spoiler summary below. You can return to any completed step without losing saved preferences."
            />

            <div className="mt-8 grid gap-4">
                <ReviewSection
                    title="Universe interests"
                    editHref={editInterests()}
                >
                    {summary.universes.length > 0
                        ? summary.universes
                              .map((universe) => universe.name)
                              .join(', ')
                        : 'No published universes were available; conservative defaults remain active.'}
                </ReviewSection>
                <ReviewSection
                    title="Initial progress"
                    editHref={editProgress()}
                >
                    {summary.progress
                        ? `${summary.progress.work} — ${summary.progress.status.replaceAll('_', ' ')}`
                        : 'No progress was changed.'}
                </ReviewSection>
                <ReviewSection
                    title="Spoiler tolerance"
                    editHref={editSpoilers()}
                >
                    {summary.spoilers.length > 0
                        ? summary.spoilers
                              .map(
                                  (item) =>
                                      `${item.universe}: ${item.tolerance}${item.warnings ? ', warnings on' : ', warnings off'}`,
                              )
                              .join('; ')
                        : 'Conservative platform behavior applies until a universe becomes available.'}
                </ReviewSection>
                <ReviewSection title="Viewing order" editHref={editOrder()}>
                    {summary.viewingOrders.length > 0
                        ? summary.viewingOrders
                              .map((item) => `${item.universe}: ${item.name}`)
                              .join('; ')
                        : 'No preferred viewing order selected.'}
                </ReviewSection>
                <ReviewSection
                    title="Privacy defaults"
                    editHref={editPrivacy()}
                >
                    <ul className="space-y-1">
                        {summary.privacy.map((item) => (
                            <li key={item} className="flex gap-2">
                                <CheckCircle2 className="mt-0.5 size-4 shrink-0 text-success" />
                                {item}
                            </li>
                        ))}
                    </ul>
                </ReviewSection>
            </div>

            <Form {...CompleteOnboardingController.form()} className="mt-8">
                {({ processing, errors }) => (
                    <>
                        <FormErrorSummary errors={errors} />
                        <input
                            type="hidden"
                            name="expected_version"
                            value={onboarding.version}
                        />
                        <OnboardingStepActions
                            backHref={onboarding.backHref}
                            processing={processing}
                            continueLabel="Complete setup"
                        />
                    </>
                )}
            </Form>
        </>
    );
}
