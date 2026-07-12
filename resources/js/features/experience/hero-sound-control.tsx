import { Volume2, VolumeX } from 'lucide-react';
import { useState } from 'react';
import { useExperience } from './experience-context';

export function HeroSoundControl() {
    const {
        visualMode,
        soundEnabled,
        ambientVolume,
        effectsVolume,
        setSoundEnabled,
        setAmbientVolume,
        setEffectsVolume,
    } = useExperience();
    const [audioUnavailable, setAudioUnavailable] = useState(false);

    const toggleSound = async (): Promise<void> => {
        try {
            await setSoundEnabled(!soundEnabled);
            setAudioUnavailable(false);
        } catch {
            setAudioUnavailable(true);
        }
    };

    return (
        <div className="hero-sound-control">
            <button
                type="button"
                onClick={() => void toggleSound()}
                disabled={visualMode === 'reduced' || audioUnavailable}
                aria-pressed={soundEnabled}
            >
                {soundEnabled ? <Volume2 /> : <VolumeX />}
                {audioUnavailable
                    ? 'Audio unavailable'
                    : soundEnabled
                      ? 'Sound on'
                      : 'Enter with sound'}
            </button>
            {soundEnabled && (
                <div className="hero-sound-mix" aria-label="Sound mix">
                    <label>
                        <span>Atmosphere</span>
                        <input
                            type="range"
                            min="0"
                            max="1"
                            step="0.05"
                            value={ambientVolume}
                            onChange={(event) =>
                                setAmbientVolume(Number(event.target.value))
                            }
                        />
                    </label>
                    <label>
                        <span>Interface</span>
                        <input
                            type="range"
                            min="0"
                            max="1"
                            step="0.05"
                            value={effectsVolume}
                            onChange={(event) =>
                                setEffectsVolume(Number(event.target.value))
                            }
                        />
                    </label>
                </div>
            )}
        </div>
    );
}
