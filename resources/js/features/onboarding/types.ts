export type OnboardingStepKey =
    | 'introduction'
    | 'universe_interests'
    | 'viewing_progress'
    | 'spoiler_preferences'
    | 'viewing_order'
    | 'privacy_defaults'
    | 'review';

export type OnboardingPageState = {
    currentStep: OnboardingStepKey;
    resumeStep: OnboardingStepKey | 'completed';
    version: number;
    startedAt: string | null;
    backHref: string | null;
    steps: Array<{
        key: OnboardingStepKey;
        label: string;
        href: string | null;
        status: 'complete' | 'current' | 'upcoming';
    }>;
};

export type OnboardingPageProps = {
    onboarding: OnboardingPageState;
};
