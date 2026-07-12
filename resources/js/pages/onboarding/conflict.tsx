import { Head, router } from '@inertiajs/react';
import { ConflictState } from '@/components/states/state-panel';
import { OnboardingStepHeader } from '@/features/onboarding/components/onboarding-step';
import type { OnboardingPageProps } from '@/features/onboarding/types';

export default function OnboardingConflict({
    onboarding,
}: OnboardingPageProps) {
    return (
        <>
            <Head title="Onboarding changed" />
            <OnboardingStepHeader
                eyebrow="Setup conflict"
                title="Your setup changed in another tab"
                description="The newer server checkpoint was kept. Reload it before making another change; no stale form data was written."
            />
            <div className="mt-8">
                <ConflictState
                    onReload={() =>
                        router.visit(
                            onboarding.steps.find(
                                (step) => step.key === onboarding.resumeStep,
                            )?.href ?? '/onboarding',
                        )
                    }
                />
            </div>
        </>
    );
}
