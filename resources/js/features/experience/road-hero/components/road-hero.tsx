import { lazy, Suspense, useEffect, useMemo, useRef, useState } from 'react';
import { useAppearance } from '@/hooks/use-appearance';
import type { ResolvedAppearance } from '@/hooks/use-appearance';
import { experienceAudio } from '../../audio-controller';
import { useExperience } from '../../experience-context';
import { SceneErrorBoundary } from '../../scene-error-boundary';
import { useRoadHeroScroll } from '../hooks/use-road-hero-scroll';
import { createRoadHeroRuntime } from '../types';
import { HeroFallback } from './hero-fallback';
import { HeroLoader } from './hero-loader';
import { HeroOverlay } from './hero-overlay';

const RoadScene = lazy(() => import('./road-scene'));

export function RoadHero() {
    const section = useRef<HTMLElement>(null);
    const runtime = useRef(createRoadHeroRuntime());
    const [sceneReady, setSceneReady] = useState(false);
    const { resolvedAppearance } = useAppearance();
    const [sceneAppearance, setSceneAppearance] =
        useState<ResolvedAppearance>('light');
    const { quality, visualMode, webglEnabled, reportWebglFailure } =
        useExperience();
    const fallbackReason = useMemo(
        () =>
            visualMode === 'reduced'
                ? 'reduced'
                : webglEnabled
                  ? 'loading'
                  : 'webgl',
        [visualMode, webglEnabled],
    );
    const useCanvas =
        webglEnabled && quality !== 'fallback' && visualMode !== 'reduced';

    useRoadHeroScroll(section, runtime, visualMode === 'reduced');

    useEffect(() => {
        const frame = requestAnimationFrame(() =>
            setSceneAppearance(resolvedAppearance),
        );

        return () => cancelAnimationFrame(frame);
    }, [resolvedAppearance]);

    useEffect(() => {
        const element = section.current;

        if (!element || !('IntersectionObserver' in window)) {
            return;
        }

        const observer = new IntersectionObserver(
            ([entry]) => {
                runtime.current.active = entry.isIntersecting;
                experienceAudio.setHeroActive(entry.isIntersecting);
            },
            { threshold: 0.05 },
        );
        observer.observe(element);

        return () => {
            observer.disconnect();
            experienceAudio.setHeroActive(false);
        };
    }, []);

    return (
        <section
            ref={section}
            className="road-hero"
            aria-labelledby="road-hero-title"
            data-appearance={sceneAppearance}
        >
            <div className="road-hero-sticky">
                {useCanvas ? (
                    <SceneErrorBoundary
                        fallback={<HeroFallback reason="webgl" />}
                        onError={reportWebglFailure}
                    >
                        <Suspense fallback={<HeroFallback reason="loading" />}>
                            <RoadScene
                                appearance={sceneAppearance}
                                quality={quality}
                                runtime={runtime}
                                onContextLost={reportWebglFailure}
                                onReady={() => setSceneReady(true)}
                            />
                        </Suspense>
                    </SceneErrorBoundary>
                ) : (
                    <HeroFallback reason={fallbackReason} />
                )}
                <div className="road-hero-vignette" aria-hidden="true" />
                <div className="road-hero-grain" aria-hidden="true" />
                <HeroOverlay />
            </div>
            <HeroLoader sceneReady={sceneReady || !useCanvas} />
        </section>
    );
}
