import { Form, Head } from '@inertiajs/react';
import { Archive, Check } from 'lucide-react';
import { update } from '@/actions/App/Http/Controllers/Onboarding/UniverseInterestsController';
import { FormErrorSummary } from '@/components/forms/form-error-summary';
import { EmptyState } from '@/components/states/state-panel';
import {
    OnboardingSelectionCard,
    OnboardingStepActions,
    OnboardingStepHeader,
} from '@/features/onboarding/components/onboarding-step';
import type { OnboardingPageProps } from '@/features/onboarding/types';

type Universe = {
    id: number;
    name: string;
    description: string | null;
    workCount: number;
    selected: boolean;
};

export default function UniverseInterests({
    onboarding,
    universes,
}: OnboardingPageProps & { universes: Universe[] }) {
    return (
        <>
            <Head title="Universe interests" />
            <OnboardingStepHeader
                eyebrow="Step 2"
                title="Which universes belong in your Archive?"
                description="Choose the published universes you want to follow. Your choices are private and only shape your personal setup."
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

                        {universes.length === 0 ? (
                            <EmptyState
                                icon={Archive}
                                title="The Archive is still being prepared"
                                description="No published universes are available. Continue with conservative spoiler and private defaults; nothing synthetic will be added."
                            />
                        ) : (
                            <fieldset>
                                <legend className="sr-only">
                                    Select universe interests
                                </legend>
                                <div className="grid gap-3 sm:grid-cols-2">
                                    {universes.map((universe) => (
                                        <label
                                            key={universe.id}
                                            className="cursor-pointer focus-within:ring-[3px] focus-within:ring-ring focus-within:outline-none"
                                        >
                                            <OnboardingSelectionCard>
                                                <div className="flex items-start gap-3">
                                                    <input
                                                        type="checkbox"
                                                        name="universe_ids[]"
                                                        value={universe.id}
                                                        defaultChecked={
                                                            universe.selected
                                                        }
                                                        className="accent-action mt-1 size-5"
                                                    />
                                                    <span className="min-w-0">
                                                        <span className="flex items-center gap-2 font-medium">
                                                            {universe.name}
                                                            {universe.selected && (
                                                                <Check className="size-4 text-success" />
                                                            )}
                                                        </span>
                                                        {universe.description && (
                                                            <span className="mt-1 block text-sm text-foreground-muted">
                                                                {
                                                                    universe.description
                                                                }
                                                            </span>
                                                        )}
                                                        <span className="text-metadata mt-3 block text-foreground-evidence">
                                                            {universe.workCount}{' '}
                                                            published{' '}
                                                            {universe.workCount ===
                                                            1
                                                                ? 'work'
                                                                : 'works'}
                                                        </span>
                                                    </span>
                                                </div>
                                            </OnboardingSelectionCard>
                                        </label>
                                    ))}
                                </div>
                            </fieldset>
                        )}

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
