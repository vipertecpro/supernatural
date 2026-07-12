import { router } from '@inertiajs/react';
import { useCallback, useEffect, useMemo, useState } from 'react';
import type { ReactNode } from 'react';
import { experienceAudio } from './audio-controller';
import {
    clampVolume,
    readExperienceCapabilities,
    resolveExperienceMode,
    resolveQuality,
    resolveVisualMode,
} from './capability-resolver';
import { ExperienceContext } from './experience-context';
import {
    startSmoothScroll,
    stopSmoothScroll,
} from './smooth-scroll-controller';
import type { ExperienceCapabilities, ExperiencePreference } from './types';

const introKey = 'archive-intro-viewed';
const conservativeCapabilities: ExperienceCapabilities = {
    reducedMotion: true,
    saveData: false,
    coarsePointer: false,
    narrowViewport: false,
    lowMemory: false,
    webgl: false,
} as const;

export function ExperienceProvider({ children }: { children: ReactNode }) {
    const [preference, setPreferenceState] =
        useState<ExperiencePreference>('full');
    const [capabilities, setCapabilities] = useState(conservativeCapabilities);
    const [webglFailed, setWebglFailed] = useState(false);
    const [soundEnabled, setSoundEnabledState] = useState(false);
    const [ambientVolume, setAmbientVolumeState] = useState(0.16);
    const [effectsVolume, setEffectsVolumeState] = useState(0.22);
    const [introComplete, setIntroComplete] = useState(true);
    const [routeTransitioning, setRouteTransitioning] = useState(false);

    const mode = resolveExperienceMode(preference, capabilities, webglFailed);
    const visualMode = resolveVisualMode(mode);
    const quality = resolveQuality(visualMode, capabilities, webglFailed);
    const smoothScrollEnabled =
        visualMode !== 'reduced' && !capabilities.coarsePointer;
    const webglEnabled = quality !== 'fallback';

    useEffect(() => {
        let mounted = true;
        queueMicrotask(() => {
            if (!mounted) {
                return;
            }

            const detected = readExperienceCapabilities();
            setCapabilities(detected);
            setIntroComplete(
                sessionStorage.getItem(introKey) === 'true' ||
                    detected.reducedMotion,
            );
        });

        const update = (): void =>
            setCapabilities(readExperienceCapabilities());
        const queries = [
            matchMedia('(prefers-reduced-motion: reduce)'),
            matchMedia('(pointer: coarse)'),
            matchMedia('(max-width: 767px)'),
        ];
        queries.forEach((query) => query.addEventListener('change', update));
        const visibility = (): void => {
            document.documentElement.dataset.pageVisible = document.hidden
                ? 'false'
                : 'true';

            if (document.hidden) {
                experienceAudio.pause();
            } else {
                experienceAudio.resume();
            }
        };
        document.addEventListener('visibilitychange', visibility);

        return () => {
            mounted = false;
            queries.forEach((query) =>
                query.removeEventListener('change', update),
            );
            document.removeEventListener('visibilitychange', visibility);
        };
    }, []);

    useEffect(() => {
        document.documentElement.dataset.experienceMode = mode;
        document.documentElement.dataset.experienceVisual = visualMode;
        document.documentElement.dataset.experienceQuality = quality;
        document.documentElement.dataset.effects =
            visualMode === 'reduced' ? 'reduced' : 'enhanced';
        document.documentElement.dataset.effectsReady = 'true';
    }, [mode, quality, visualMode]);

    useEffect(() => {
        if (smoothScrollEnabled) {
            void startSmoothScroll();
        } else {
            stopSmoothScroll();
        }

        return stopSmoothScroll;
    }, [smoothScrollEnabled]);

    useEffect(() => {
        const offStart = router.on('start', () => {
            setRouteTransitioning(true);
            experienceAudio.pause();
        });
        const offFinish = router.on('finish', () => {
            setRouteTransitioning(false);
            experienceAudio.resume();
        });
        const offNavigate = router.on('navigate', () => {
            setRouteTransitioning(false);
            requestAnimationFrame(() =>
                document.querySelector<HTMLElement>('#main-content')?.focus(),
            );
        });

        return () => {
            offStart();
            offFinish();
            offNavigate();
        };
    }, []);

    const setPreference = useCallback((next: ExperiencePreference): void => {
        setPreferenceState(next);

        if (next === 'silent' || next === 'reduced') {
            experienceAudio.disable();
            setSoundEnabledState(false);
        }
    }, []);

    const setSoundEnabled = useCallback(
        async (enabled: boolean): Promise<void> => {
            if (!enabled || mode === 'silent' || visualMode === 'reduced') {
                experienceAudio.disable();
                setSoundEnabledState(false);

                return;
            }

            await experienceAudio.enable();
            setSoundEnabledState(true);
        },
        [mode, visualMode],
    );

    const setAmbientVolume = useCallback((volume: number): void => {
        const next = clampVolume(volume);
        setAmbientVolumeState(next);
        experienceAudio.setAmbientVolume(next);
    }, []);

    const setEffectsVolume = useCallback((volume: number): void => {
        const next = clampVolume(volume);
        setEffectsVolumeState(next);
        experienceAudio.setEffectsVolume(next);
    }, []);

    const completeIntro = useCallback((): void => {
        sessionStorage.setItem(introKey, 'true');
        setIntroComplete(true);
        requestAnimationFrame(() =>
            document.querySelector<HTMLElement>('#road-hero-title')?.focus(),
        );
    }, []);

    const value = useMemo(
        () => ({
            preference,
            mode,
            visualMode,
            quality,
            soundEnabled,
            ambientVolume,
            effectsVolume,
            smoothScrollEnabled,
            webglEnabled,
            introComplete,
            routeTransitioning,
            setPreference,
            setSoundEnabled,
            setAmbientVolume,
            setEffectsVolume,
            completeIntro,
            reportWebglFailure: () => setWebglFailed(true),
            playInterfaceSound:
                experienceAudio.playInterfaceSound.bind(experienceAudio),
        }),
        [
            ambientVolume,
            completeIntro,
            effectsVolume,
            introComplete,
            mode,
            preference,
            quality,
            routeTransitioning,
            setAmbientVolume,
            setEffectsVolume,
            setPreference,
            setSoundEnabled,
            smoothScrollEnabled,
            soundEnabled,
            visualMode,
            webglEnabled,
        ],
    );

    return (
        <ExperienceContext.Provider value={value}>
            {children}
            <div
                className="experience-route-transition"
                data-active={routeTransitioning}
                aria-hidden="true"
            >
                <span />
                <span />
            </div>
        </ExperienceContext.Provider>
    );
}
