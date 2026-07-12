export type ExperienceMode = 'full' | 'balanced' | 'reduced' | 'silent';

export type VisualExperienceMode = Exclude<ExperienceMode, 'silent'>;

export type ExperienceQuality = 'high' | 'medium' | 'low' | 'fallback';

export type ExperiencePreference = ExperienceMode | 'automatic';

export type ExperienceCapabilities = {
    reducedMotion: boolean;
    saveData: boolean;
    coarsePointer: boolean;
    narrowViewport: boolean;
    lowMemory: boolean;
    webgl: boolean;
};

export type ExperienceSettings = {
    preference: ExperiencePreference;
    mode: ExperienceMode;
    visualMode: VisualExperienceMode;
    quality: ExperienceQuality;
    soundEnabled: boolean;
    ambientVolume: number;
    effectsVolume: number;
    smoothScrollEnabled: boolean;
    webglEnabled: boolean;
};

export type ExperienceContextValue = ExperienceSettings & {
    introComplete: boolean;
    routeTransitioning: boolean;
    setPreference: (preference: ExperiencePreference) => void;
    setSoundEnabled: (enabled: boolean) => Promise<void>;
    setAmbientVolume: (volume: number) => void;
    setEffectsVolume: (volume: number) => void;
    completeIntro: () => void;
    reportWebglFailure: () => void;
    playInterfaceSound: (kind?: 'select' | 'open' | 'close') => void;
};
