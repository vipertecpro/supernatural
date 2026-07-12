import { Form, Head } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { update } from '@/actions/App/Http/Controllers/Onboarding/ViewingProgressController';
import { FormErrorSummary } from '@/components/forms/form-error-summary';
import { EmptyState } from '@/components/states/state-panel';
import { Label } from '@/components/ui/label';
import {
    OnboardingSelectionCard,
    OnboardingStepActions,
    OnboardingStepHeader,
} from '@/features/onboarding/components/onboarding-step';
import type { OnboardingPageProps } from '@/features/onboarding/types';

type Episode = { id: number; label: string };
type Work = {
    id: number;
    universeId: number;
    universeName: string;
    title: string;
    standaloneEpisodes: Episode[];
    seasons: Array<{ id: number; label: string; episodes: Episode[] }>;
};

const modes = [
    {
        value: 'not_started',
        title: 'I have not started',
        description: 'Keep this work behind the safest spoiler boundary.',
    },
    {
        value: 'watched_through',
        title: 'I watched through an episode',
        description:
            'Mark the bounded published episode sequence through your selection complete.',
    },
    {
        value: 'completed_work',
        title: 'I completed this work',
        description:
            'Record work-level completion through the existing progress action.',
    },
    {
        value: 'skip',
        title: 'Skip for now',
        description:
            'Leave progress unchanged and retain conservative spoiler behavior.',
    },
] as const;

export default function ViewingProgress({
    onboarding,
    works,
}: OnboardingPageProps & { works: Work[] }) {
    const [mode, setMode] = useState<(typeof modes)[number]['value']>(
        works.length === 0 ? 'skip' : 'not_started',
    );
    const [workId, setWorkId] = useState(works[0]?.id.toString() ?? '');
    const selectedWork = works.find((work) => work.id.toString() === workId);
    const [seasonId, setSeasonId] = useState(
        selectedWork?.seasons[0]?.id.toString() ?? '',
    );
    const episodes = useMemo(() => {
        if (!selectedWork) {
            return [];
        }

        if (seasonId) {
            return (
                selectedWork.seasons.find(
                    (season) => season.id.toString() === seasonId,
                )?.episodes ?? []
            );
        }

        return selectedWork.standaloneEpisodes;
    }, [seasonId, selectedWork]);

    const chooseWork = (value: string) => {
        setWorkId(value);
        const work = works.find(
            (candidate) => candidate.id.toString() === value,
        );
        setSeasonId(work?.seasons[0]?.id.toString() ?? '');
    };

    return (
        <>
            <Head title="Initial viewing progress" />
            <OnboardingStepHeader
                eyebrow="Step 3"
                title="Set an initial viewing boundary"
                description="Progress is private. Only published identifiers are used, and episode summaries or future story details are never loaded here."
            />

            <Form {...update.form()} className="mt-8">
                {({ processing, errors }) => (
                    <div className="space-y-7">
                        <FormErrorSummary errors={errors} />
                        <input
                            type="hidden"
                            name="expected_version"
                            value={onboarding.version}
                        />

                        {works.length === 0 && (
                            <EmptyState
                                title="No published works are available"
                                description="Progress setup will be skipped without creating placeholder content. Your spoiler policy stays conservative."
                            />
                        )}

                        <fieldset>
                            <legend className="font-medium">
                                Choose your progress state
                            </legend>
                            <div className="mt-3 grid gap-3 sm:grid-cols-2">
                                {modes.map((option) => (
                                    <label
                                        key={option.value}
                                        className="cursor-pointer focus-within:ring-[3px] focus-within:ring-ring"
                                    >
                                        <OnboardingSelectionCard
                                            selected={mode === option.value}
                                            className="h-full"
                                        >
                                            <span className="flex gap-3">
                                                <input
                                                    type="radio"
                                                    name="mode"
                                                    value={option.value}
                                                    checked={
                                                        mode === option.value
                                                    }
                                                    onChange={() =>
                                                        setMode(option.value)
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

                        {mode !== 'skip' && works.length > 0 && (
                            <div className="grid gap-5 rounded-xl border border-border bg-surface-secondary/40 p-5">
                                <div className="grid gap-2">
                                    <Label htmlFor="work_id">
                                        Published work
                                    </Label>
                                    <select
                                        id="work_id"
                                        name="work_id"
                                        value={workId}
                                        onChange={(event) =>
                                            chooseWork(event.target.value)
                                        }
                                        className="min-h-11 rounded-md border border-input bg-background px-3"
                                    >
                                        {works.map((work) => (
                                            <option
                                                key={work.id}
                                                value={work.id}
                                            >
                                                {work.universeName} —{' '}
                                                {work.title}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                {mode === 'watched_through' && selectedWork && (
                                    <>
                                        {selectedWork.seasons.length > 0 && (
                                            <div className="grid gap-2">
                                                <Label htmlFor="season_id">
                                                    Season
                                                </Label>
                                                <select
                                                    id="season_id"
                                                    value={seasonId}
                                                    onChange={(event) =>
                                                        setSeasonId(
                                                            event.target.value,
                                                        )
                                                    }
                                                    className="min-h-11 rounded-md border border-input bg-background px-3"
                                                >
                                                    {selectedWork.seasons.map(
                                                        (season) => (
                                                            <option
                                                                key={season.id}
                                                                value={
                                                                    season.id
                                                                }
                                                            >
                                                                {season.label}
                                                            </option>
                                                        ),
                                                    )}
                                                </select>
                                            </div>
                                        )}
                                        <div className="grid gap-2">
                                            <Label htmlFor="episode_id">
                                                Latest completed episode
                                            </Label>
                                            <select
                                                id="episode_id"
                                                name="episode_id"
                                                defaultValue=""
                                                className="min-h-11 rounded-md border border-input bg-background px-3"
                                            >
                                                <option value="" disabled>
                                                    Select an episode
                                                </option>
                                                {episodes.map((episode) => (
                                                    <option
                                                        key={episode.id}
                                                        value={episode.id}
                                                    >
                                                        {episode.label}
                                                    </option>
                                                ))}
                                            </select>
                                            {episodes.length === 0 && (
                                                <p className="text-sm text-foreground-muted">
                                                    This published selection has
                                                    no available episodes.
                                                    Choose another state or
                                                    skip.
                                                </p>
                                            )}
                                        </div>
                                    </>
                                )}
                            </div>
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
