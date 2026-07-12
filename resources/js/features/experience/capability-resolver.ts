import type {
    ExperienceCapabilities,
    ExperienceMode,
    ExperiencePreference,
    ExperienceQuality,
    VisualExperienceMode,
} from './types';

type NavigatorWithHints = Navigator & {
    connection?: { saveData?: boolean };
    deviceMemory?: number;
};

export const detectWebglSupport = (): boolean => {
    if (typeof document === 'undefined') {
        return false;
    }

    try {
        const canvas = document.createElement('canvas');

        return Boolean(
            canvas.getContext('webgl2') || canvas.getContext('webgl'),
        );
    } catch {
        return false;
    }
};

export const readExperienceCapabilities = (): ExperienceCapabilities => {
    if (typeof window === 'undefined') {
        return {
            reducedMotion: true,
            saveData: false,
            coarsePointer: false,
            narrowViewport: false,
            lowMemory: false,
            webgl: false,
        };
    }

    const hints = navigator as NavigatorWithHints;
    const reviewMode = window.location.hostname.endsWith('.test')
        ? new URLSearchParams(window.location.search).get('roadHeroReview')
        : null;
    const forceMotion =
        reviewMode === 'full' ||
        reviewMode === 'motion' ||
        reviewMode === 'fallback';

    return {
        reducedMotion:
            !forceMotion &&
            window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        saveData: !forceMotion && hints.connection?.saveData === true,
        coarsePointer:
            reviewMode === 'full'
                ? false
                : window.matchMedia('(pointer: coarse)').matches,
        narrowViewport:
            reviewMode === 'full'
                ? false
                : window.matchMedia('(max-width: 767px)').matches,
        lowMemory:
            reviewMode === 'full'
                ? false
                : hints.deviceMemory !== undefined && hints.deviceMemory <= 4,
        webgl: reviewMode !== 'fallback' && detectWebglSupport(),
    };
};

export const resolveExperienceMode = (
    preference: ExperiencePreference,
    capabilities: ExperienceCapabilities,
    webglFailed = false,
): ExperienceMode => {
    if (preference !== 'automatic') {
        return preference;
    }

    if (capabilities.reducedMotion || capabilities.saveData) {
        return 'reduced';
    }

    if (
        webglFailed ||
        !capabilities.webgl ||
        capabilities.coarsePointer ||
        capabilities.narrowViewport ||
        capabilities.lowMemory
    ) {
        return 'balanced';
    }

    return 'full';
};

export const resolveVisualMode = (
    mode: ExperienceMode,
): VisualExperienceMode => {
    return mode === 'silent' ? 'balanced' : mode;
};

export const resolveQuality = (
    mode: VisualExperienceMode,
    capabilities: ExperienceCapabilities,
    webglFailed = false,
): ExperienceQuality => {
    if (webglFailed || !capabilities.webgl || mode === 'reduced') {
        return 'fallback';
    }

    if (capabilities.narrowViewport || capabilities.coarsePointer) {
        return 'low';
    }

    if (mode === 'full' && !capabilities.lowMemory) {
        return 'high';
    }

    return 'medium';
};

export const clampVolume = (volume: number): number =>
    Math.min(1, Math.max(0, volume));
