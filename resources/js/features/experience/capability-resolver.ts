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

    return {
        reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)')
            .matches,
        saveData: hints.connection?.saveData === true,
        coarsePointer: window.matchMedia('(pointer: coarse)').matches,
        narrowViewport: window.matchMedia('(max-width: 767px)').matches,
        lowMemory: hints.deviceMemory !== undefined && hints.deviceMemory <= 4,
        webgl: detectWebglSupport(),
    };
};

export const resolveExperienceMode = (
    preference: ExperiencePreference,
    capabilities: ExperienceCapabilities,
    webglFailed = false,
): ExperienceMode => {
    if (capabilities.reducedMotion || capabilities.saveData) {
        return 'reduced';
    }

    if (preference !== 'automatic') {
        return preference;
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
    capabilities: ExperienceCapabilities,
): VisualExperienceMode => {
    if (capabilities.reducedMotion || capabilities.saveData) {
        return 'reduced';
    }

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

    if (mode === 'full' && !capabilities.lowMemory) {
        return 'high';
    }

    return capabilities.narrowViewport || capabilities.coarsePointer
        ? 'low'
        : 'medium';
};

export const clampVolume = (volume: number): number =>
    Math.min(1, Math.max(0, volume));
