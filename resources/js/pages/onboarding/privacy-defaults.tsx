import { Form, Head } from '@inertiajs/react';
import { LockKeyhole, ShieldCheck } from 'lucide-react';
import { update } from '@/actions/App/Http/Controllers/Onboarding/PrivacyDefaultsController';
import { FormErrorSummary } from '@/components/forms/form-error-summary';
import {
    OnboardingStepActions,
    OnboardingStepHeader,
} from '@/features/onboarding/components/onboarding-step';
import type { OnboardingPageProps } from '@/features/onboarding/types';

const privateDefaults = [
    ['Viewing progress and Continue Watching', 'Private to you'],
    ['Journey', 'Private to you'],
    ['Favourites and ratings', 'Private to you'],
    ['Watchlists and personal notes', 'Always private in the current platform'],
] as const;

export default function PrivacyDefaults({ onboarding }: OnboardingPageProps) {
    return (
        <>
            <Head title="Privacy defaults" />
            <OnboardingStepHeader
                eyebrow="Step 6"
                title="Confirm privacy-preserving defaults"
                description="The current typed preference model supports private defaults only. No unsupported public visibility, marketing consent, or arbitrary JSON setting is created."
            />

            <Form {...update.form()} className="mt-8">
                {({ processing, errors }) => (
                    <div className="space-y-6">
                        <FormErrorSummary errors={errors} />
                        <input
                            type="hidden"
                            name="expected_version"
                            value={onboarding.version}
                        />

                        <dl className="divide-y divide-border rounded-xl border border-border-strong">
                            {privateDefaults.map(([label, value]) => (
                                <div
                                    key={label}
                                    className="grid gap-1 p-4 sm:grid-cols-[1fr_auto] sm:items-center"
                                >
                                    <dt className="font-medium">{label}</dt>
                                    <dd className="inline-flex items-center gap-2 text-sm text-success">
                                        <LockKeyhole className="size-4" />
                                        {value}
                                    </dd>
                                </div>
                            ))}
                        </dl>

                        <div className="rounded-xl border border-information bg-surface-secondary/50 p-4 text-sm">
                            <p className="flex items-center gap-2 font-medium">
                                <ShieldCheck className="size-4" />
                                Community safety boundary
                            </p>
                            <p className="mt-2 text-foreground-muted">
                                Blocks and mutes are always private. Private
                                group membership follows that group's visibility
                                policy. No unsupported Community or notification
                                default is shown here.
                            </p>
                        </div>

                        <label className="flex min-h-11 cursor-pointer items-start gap-3 rounded-xl border border-border p-4 focus-within:ring-[3px] focus-within:ring-ring">
                            <input
                                type="checkbox"
                                name="confirm_private_defaults"
                                value="1"
                                className="accent-action mt-1 size-5"
                            />
                            <span>
                                <span className="block font-medium">
                                    Keep these privacy-preserving defaults
                                </span>
                                <span className="mt-1 block text-sm text-foreground-muted">
                                    This explicit confirmation is required
                                    before review.
                                </span>
                            </span>
                        </label>

                        <OnboardingStepActions
                            backHref={onboarding.backHref}
                            processing={processing}
                        />
                    </div>
                )}
            </Form>
        </>
    );
}
