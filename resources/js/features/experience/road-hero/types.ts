import type { MutableRefObject } from 'react';
import type { ResolvedAppearance } from '@/hooks/use-appearance';
import type { ExperienceQuality } from '../types';

export type RoadHeroRuntime = {
    progress: number;
    previousProgress: number;
    velocity: number;
    active: boolean;
};

export type RoadHeroSceneProps = {
    appearance: ResolvedAppearance;
    quality: Exclude<ExperienceQuality, 'fallback'>;
    runtime: MutableRefObject<RoadHeroRuntime>;
    onContextLost: () => void;
    onReady: () => void;
};

export const createRoadHeroRuntime = (): RoadHeroRuntime => ({
    progress: 0,
    previousProgress: 0,
    velocity: 0,
    active: true,
});
