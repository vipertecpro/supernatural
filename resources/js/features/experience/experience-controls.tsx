import { SlidersHorizontal, Volume2, VolumeX } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { useExperience } from './experience-context';
import type { ExperiencePreference } from './types';

const modes: Array<{ value: ExperiencePreference; label: string }> = [
    { value: 'automatic', label: 'Automatic' },
    { value: 'full', label: 'Full' },
    { value: 'balanced', label: 'Balanced' },
    { value: 'reduced', label: 'Reduced' },
    { value: 'silent', label: 'Silent' },
];

export function ExperienceControls() {
    const [open, setOpen] = useState(false);
    const experience = useExperience();

    return (
        <div className="experience-controls">
            <Button
                variant="ghost"
                size="icon"
                aria-label="Experience settings"
                aria-expanded={open}
                aria-controls="experience-settings-panel"
                onClick={() => setOpen((current) => !current)}
            >
                <SlidersHorizontal />
            </Button>
            {open && (
                <div
                    id="experience-settings-panel"
                    className="experience-controls-panel"
                    data-native-scroll="true"
                >
                    <div>
                        <p className="text-metadata">EXPERIENCE</p>
                        <p className="mt-1 text-sm text-foreground-secondary">
                            Current: {experience.mode}
                        </p>
                    </div>
                    <label className="grid gap-2 text-sm">
                        Motion and visuals
                        <select
                            className="h-10 rounded-md border bg-background px-3"
                            value={experience.preference}
                            onChange={(event) =>
                                experience.setPreference(
                                    event.target.value as ExperiencePreference,
                                )
                            }
                        >
                            {modes.map((mode) => (
                                <option key={mode.value} value={mode.value}>
                                    {mode.label}
                                </option>
                            ))}
                        </select>
                    </label>
                    <Button
                        variant="outline"
                        className="justify-start"
                        disabled={
                            experience.mode === 'silent' ||
                            experience.visualMode === 'reduced'
                        }
                        onClick={() =>
                            void experience.setSoundEnabled(
                                !experience.soundEnabled,
                            )
                        }
                    >
                        {experience.soundEnabled ? <Volume2 /> : <VolumeX />}
                        Sound {experience.soundEnabled ? 'on' : 'off'}
                    </Button>
                    <label className="grid gap-2 text-sm">
                        Ambient volume
                        <input
                            type="range"
                            min="0"
                            max="1"
                            step="0.01"
                            value={experience.ambientVolume}
                            disabled={!experience.soundEnabled}
                            onChange={(event) =>
                                experience.setAmbientVolume(
                                    Number(event.target.value),
                                )
                            }
                        />
                    </label>
                    <label className="grid gap-2 text-sm">
                        Interface volume
                        <input
                            type="range"
                            min="0"
                            max="1"
                            step="0.01"
                            value={experience.effectsVolume}
                            disabled={!experience.soundEnabled}
                            onChange={(event) =>
                                experience.setEffectsVolume(
                                    Number(event.target.value),
                                )
                            }
                        />
                    </label>
                    <p className="text-xs leading-5 text-foreground-muted">
                        Sound is always muted until you enable it. Capability
                        checks stay on this device.
                    </p>
                </div>
            )}
        </div>
    );
}
