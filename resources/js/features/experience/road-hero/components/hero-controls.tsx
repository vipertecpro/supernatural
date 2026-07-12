import { SlidersHorizontal, Volume2, VolumeX } from 'lucide-react';
import { useState } from 'react';
import { useExperience } from '../../experience-context';

export function HeroControls() {
    const experience = useExperience();
    const [mixOpen, setMixOpen] = useState(false);
    const [audioUnavailable, setAudioUnavailable] = useState(false);

    const toggleSound = async (): Promise<void> => {
        try {
            await experience.setSoundEnabled(!experience.soundEnabled);
            setAudioUnavailable(false);
        } catch {
            setAudioUnavailable(true);
        }
    };

    return (
        <div className="road-hero-controls" data-road-hero-reveal>
            <button
                type="button"
                onClick={() => void toggleSound()}
                disabled={
                    experience.visualMode === 'reduced' || audioUnavailable
                }
                aria-pressed={experience.soundEnabled}
                aria-label={
                    audioUnavailable
                        ? 'Audio unavailable'
                        : experience.soundEnabled
                          ? 'Mute ambient sound'
                          : 'Enable ambient sound'
                }
            >
                {experience.soundEnabled ? <Volume2 /> : <VolumeX />}
                <span>
                    {audioUnavailable
                        ? 'Unavailable'
                        : experience.soundEnabled
                          ? 'Sound on'
                          : 'Sound off'}
                </span>
            </button>
            <button
                type="button"
                onClick={() => setMixOpen((current) => !current)}
                aria-expanded={mixOpen}
                aria-controls="road-hero-sound-mix"
                disabled={!experience.soundEnabled}
            >
                <SlidersHorizontal />
                <span>Mix</span>
            </button>
            {mixOpen && experience.soundEnabled && (
                <div id="road-hero-sound-mix" className="road-hero-sound-mix">
                    <label>
                        <span>Ambient</span>
                        <input
                            type="range"
                            min="0"
                            max="1"
                            step="0.05"
                            value={experience.ambientVolume}
                            onChange={(event) =>
                                experience.setAmbientVolume(
                                    Number(event.target.value),
                                )
                            }
                        />
                    </label>
                    <label>
                        <span>Effects</span>
                        <input
                            type="range"
                            min="0"
                            max="1"
                            step="0.05"
                            value={experience.effectsVolume}
                            onChange={(event) =>
                                experience.setEffectsVolume(
                                    Number(event.target.value),
                                )
                            }
                        />
                    </label>
                </div>
            )}
        </div>
    );
}
