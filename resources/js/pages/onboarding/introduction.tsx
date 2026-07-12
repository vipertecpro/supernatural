import { Form, Head } from '@inertiajs/react';
import { EyeOff, LockKeyhole, SlidersHorizontal } from 'lucide-react';
import { update } from '@/actions/App/Http/Controllers/Onboarding/IntroductionController';
import { FormErrorSummary } from '@/components/forms/form-error-summary';
import {
    OnboardingStepActions,
    OnboardingStepHeader,
} from '@/features/onboarding/components/onboarding-step';
import type { OnboardingPageProps } from '@/features/onboarding/types';

export default function Introduction({ onboarding }: OnboardingPageProps) {
    return (
        <>
            <Head title="Set up your Archive" />
            <OnboardingStepHeader
                eyebrow="Welcome to your Archive"
                title="Set your spoiler-safe starting point"
                description="Seven short decisions configure the universes you follow, what you have watched, and the privacy rules for your personal journey."
            />

            <div className="mt-8 grid gap-4 md:grid-cols-3">
                <div className="rounded-xl border border-border p-4">
                    <EyeOff className="size-5 text-warning" />
                    <h2 className="mt-3 font-medium">Spoiler safety</h2>
                    <p className="mt-1 text-sm text-foreground-muted">
                        Progress gives the server a conservative boundary for
                        what may be shown.
                    </p>
                </div>
                <div className="rounded-xl border border-border p-4">
                    <LockKeyhole className="size-5 text-success" />
                    <h2 className="mt-3 font-medium">Private by default</h2>
                    <p className="mt-1 text-sm text-foreground-muted">
                        Viewing progress, Journey, favourites, ratings, notes,
                        blocks, and mutes stay private.
                    </p>
                </div>
                <div className="rounded-xl border border-border p-4">
                    <SlidersHorizontal className="size-5 text-information" />
                    <h2 className="mt-3 font-medium">Change it later</h2>
                    <p className="mt-1 text-sm text-foreground-muted">
                        These defaults can be adjusted from your account as
                        those settings become available.
                    </p>
                </div>
            </div>

            <Form {...update.form()} className="mt-8">
                {({ processing, errors }) => (
                    <>
                        <FormErrorSummary errors={errors} />
                        <input
                            type="hidden"
                            name="expected_version"
                            value={onboarding.version}
                        />
                        <OnboardingStepActions
                            backHref={null}
                            processing={processing}
                            continueLabel="Begin setup"
                        />
                    </>
                )}
            </Form>
        </>
    );
}
