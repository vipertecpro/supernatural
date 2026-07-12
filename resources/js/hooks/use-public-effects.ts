import { useEffect, useSyncExternalStore } from 'react';

export type EffectsPreference = 'automatic' | 'enhanced' | 'reduced';
export type EffectiveEffects = 'enhanced' | 'reduced';

type NavigatorWithHints = Navigator & {
    connection?: { saveData?: boolean };
    deviceMemory?: number;
};

const storageKey = 'public-effects';
const listeners = new Set<() => void>();
let preference: EffectsPreference = 'automatic';
let initialized = false;

const mediaQueries = (): MediaQueryList[] => {
    if (typeof window === 'undefined') {
        return [];
    }

    return [
        window.matchMedia('(prefers-reduced-motion: reduce)'),
        window.matchMedia('(pointer: coarse)'),
        window.matchMedia('(max-width: 767px)'),
    ];
};

const isStoredPreference = (value: string | null): value is EffectsPreference =>
    value === 'automatic' || value === 'enhanced' || value === 'reduced';

export const resolveEffectsPreference = (
    selected: EffectsPreference,
): EffectiveEffects => {
    if (typeof window === 'undefined') {
        return 'reduced';
    }

    const navigatorHints = navigator as NavigatorWithHints;
    const requiresReduction =
        window.matchMedia('(prefers-reduced-motion: reduce)').matches ||
        navigatorHints.connection?.saveData === true;

    if (selected === 'reduced' || requiresReduction) {
        return 'reduced';
    }

    if (selected === 'enhanced') {
        return 'enhanced';
    }

    const automaticReduction =
        window.matchMedia('(pointer: coarse)').matches ||
        window.matchMedia('(max-width: 767px)').matches ||
        (navigatorHints.deviceMemory !== undefined &&
            navigatorHints.deviceMemory <= 4);

    return automaticReduction ? 'reduced' : 'enhanced';
};

const applyPreference = (): void => {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.dataset.effectsPreference = preference;
    document.documentElement.dataset.effects =
        resolveEffectsPreference(preference);
    document.documentElement.dataset.effectsReady = 'true';
    document.documentElement.dataset.pageVisible = document.hidden
        ? 'false'
        : 'true';
};

const notify = (): void => {
    applyPreference();
    listeners.forEach((listener) => listener());
};

const initialize = (): void => {
    if (initialized || typeof window === 'undefined') {
        return;
    }

    initialized = true;
    const stored = localStorage.getItem(storageKey);
    preference = isStoredPreference(stored) ? stored : 'automatic';
    mediaQueries().forEach((query) => query.addEventListener('change', notify));
    document.addEventListener('visibilitychange', applyPreference);
    applyPreference();
};

const subscribe = (listener: () => void): (() => void) => {
    listeners.add(listener);

    return () => listeners.delete(listener);
};

export function usePublicEffects() {
    useEffect(initialize, []);

    const selected = useSyncExternalStore<EffectsPreference>(
        subscribe,
        () => preference,
        () => 'automatic',
    );

    const updatePreference = (next: EffectsPreference): void => {
        preference = next;
        localStorage.setItem(storageKey, next);
        notify();
    };

    return {
        preference: selected,
        effectiveEffects: resolveEffectsPreference(selected),
        updatePreference,
    } as const;
}
