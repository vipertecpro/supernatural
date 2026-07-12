import { Form, Head } from '@inertiajs/react';
import { update } from '@/actions/App/Http/Controllers/Onboarding/SpoilerPreferencesController';
import { FormErrorSummary } from '@/components/forms/form-error-summary';
import { SpoilerBadge } from '@/components/spoiler/spoiler-states';
import {
    OnboardingSelectionCard,
    OnboardingStepActions,
    OnboardingStepHeader,
} from '@/features/onboarding/components/onboarding-step';
import type { OnboardingPageProps } from '@/features/onboarding/types';

const toleranceOptions = [
    {
        value: 'strict',
        title: 'Conservative',
        description:
            'Redact unreached spoilers and hide finale-level content. This is the safest policy.',
    },
    {
        value: 'warn',
        title: 'Warn before revealing',
        description:
            'Minor and moderate spoilers may appear behind warnings; major and finale details remain withheld.',
    },
    {
        value: 'permissive',
        title: 'Permissive with warnings',
        description:
            'Unreached classified spoilers may be shown only with an explicit warning.',
    },
] as const;

export default function SpoilerPreferences({
    onboarding,
    preference,
}: OnboardingPageProps & {
    preference: {
        tolerance: string;
        showWarnings: boolean;
        rewatchBehavior: string;
    };
}) {
    return (
        <>
            <Head title="Spoiler preferences" />
            <OnboardingStepHeader
                eyebrow="Step 4"
                title="Choose what is safe to reveal"
                description="The server applies this policy before protected text reaches the page. Missing classifications remain conservatively unavailable."
            />

            <div
                className="mt-6 flex flex-wrap gap-2"
                aria-label="Spoiler severity scale"
            >
                <SpoilerBadge severity="safe" />
                <SpoilerBadge severity="minor" />
                <SpoilerBadge severity="moderate" />
                <SpoilerBadge severity="major" />
                <SpoilerBadge severity="finale" />
            </div>

            <Form {...update.form()} className="mt-8">
                {({ processing, errors }) => (
                    <div className="space-y-7">
                        <FormErrorSummary errors={errors} />
                        <input
                            type="hidden"
                            name="expected_version"
                            value={onboarding.version}
                        />

                        <fieldset>
                            <legend className="font-medium">
                                Spoiler tolerance
                            </legend>
                            <div className="mt-3 grid gap-3">
                                {toleranceOptions.map((option) => (
                                    <label
                                        key={option.value}
                                        className="cursor-pointer focus-within:ring-[3px] focus-within:ring-ring"
                                    >
                                        <OnboardingSelectionCard>
                                            <span className="flex gap-3">
                                                <input
                                                    type="radio"
                                                    name="tolerance"
                                                    value={option.value}
                                                    defaultChecked={
                                                        preference.tolerance ===
                                                        option.value
                                                    }
                                                    className="accent-action mt-1 size-5"
                                                />
                                                <span>
                                                    <span className="block font-medium">
                                                        {option.title}
                                                    </span>
                                                    <span className="mt-1 block text-sm text-foreground-muted">
                                                        {option.description}
                                                    </span>
                                                </span>
                                            </span>
                                        </OnboardingSelectionCard>
                                    </label>
                                ))}
                            </div>
                        </fieldset>

                        <div className="rounded-xl border border-border p-4">
                            <label className="flex min-h-11 cursor-pointer items-start gap-3">
                                <input
                                    type="checkbox"
                                    name="show_warnings"
                                    value="1"
                                    defaultChecked={preference.showWarnings}
                                    className="accent-action mt-1 size-5"
                                />
                                <span>
                                    <span className="block font-medium">
                                        Show safe warning metadata
                                    </span>
                                    <span className="mt-1 block text-sm text-foreground-muted">
                                        Warnings may identify severity and the
                                        required viewing boundary, but never
                                        include protected story text.
                                    </span>
                                </span>
                            </label>
                        </div>

                        <fieldset>
                            <legend className="font-medium">
                                Rewatch behavior
                            </legend>
                            <div className="mt-3 grid gap-3 sm:grid-cols-2">
                                <label className="rounded-xl border border-border p-4">
                                    <input
                                        type="radio"
                                        name="rewatch_behavior"
                                        value="historical"
                                        defaultChecked={
                                            preference.rewatchBehavior ===
                                            'historical'
                                        }
                                        className="accent-action mr-3 size-5"
                                    />
                                    Use furthest historical knowledge
                                </label>
                                <label className="rounded-xl border border-border p-4">
                                    <input
                                        type="radio"
                                        name="rewatch_behavior"
                                        value="current_cycle"
                                        defaultChecked={
                                            preference.rewatchBehavior ===
                                            'current_cycle'
                                        }
                                        className="accent-action mr-3 size-5"
                                    />
                                    Follow the current rewatch cycle
                                </label>
                            </div>
                        </fieldset>

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
