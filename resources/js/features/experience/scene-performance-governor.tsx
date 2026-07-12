import { useFrame } from '@react-three/fiber';
import { useRef } from 'react';
import type { ExperienceQuality } from './types';

export function lowerExperienceQuality(
    quality: ExperienceQuality,
): ExperienceQuality {
    if (quality === 'high') {
        return 'medium';
    }

    if (quality === 'medium') {
        return 'low';
    }

    return quality;
}

export function applyExperienceQualityDrops(
    quality: ExperienceQuality,
    drops: number,
): ExperienceQuality {
    let current = quality;

    for (let index = 0; index < drops; index += 1) {
        current = lowerExperienceQuality(current);
    }

    return current;
}

export function ScenePerformanceGovernor({
    active,
    quality,
    onPressure,
}: {
    active: boolean;
    quality: ExperienceQuality;
    onPressure: () => void;
}) {
    const elapsed = useRef(0);
    const frames = useRef(0);
    const reported = useRef(false);

    useFrame((_, delta) => {
        if (
            !active ||
            document.hidden ||
            reported.current ||
            quality === 'low' ||
            quality === 'fallback' ||
            delta > 0.1
        ) {
            return;
        }

        elapsed.current += delta;
        frames.current += 1;

        if (elapsed.current < 5) {
            return;
        }

        const framesPerSecond = frames.current / elapsed.current;
        elapsed.current = 0;
        frames.current = 0;

        if (framesPerSecond < 42) {
            reported.current = true;
            queueMicrotask(onPressure);
        }
    });

    return null;
}
