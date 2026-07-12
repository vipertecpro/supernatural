import { clampVolume } from './capability-resolver';

class ExperienceAudioController {
    private context: AudioContext | null = null;
    private ambientGain: GainNode | null = null;
    private effectsGain: GainNode | null = null;
    private drone: OscillatorNode | null = null;
    private wind: AudioBufferSourceNode | null = null;
    private enabled = false;
    private ambientVolume = 0.16;
    private effectsVolume = 0.22;

    public async enable(): Promise<void> {
        if (this.enabled) {
            return;
        }

        const AudioContextConstructor =
            window.AudioContext ??
            (
                window as typeof window & {
                    webkitAudioContext?: typeof AudioContext;
                }
            ).webkitAudioContext;

        if (!AudioContextConstructor) {
            throw new Error('Audio is unavailable in this browser.');
        }

        this.context ??= new AudioContextConstructor();
        await this.context.resume();
        this.enabled = true;
        this.startAtmosphere();
    }

    public disable(): void {
        this.enabled = false;
        this.drone?.stop();
        this.wind?.stop();
        this.drone = null;
        this.wind = null;
    }

    public setAmbientVolume(volume: number): void {
        this.ambientVolume = clampVolume(volume);
        this.ambientGain?.gain.setTargetAtTime(
            this.ambientVolume,
            this.context?.currentTime ?? 0,
            0.08,
        );
    }

    public setEffectsVolume(volume: number): void {
        this.effectsVolume = clampVolume(volume);
        this.effectsGain?.gain.setTargetAtTime(
            this.effectsVolume,
            this.context?.currentTime ?? 0,
            0.04,
        );
    }

    public playInterfaceSound(
        kind: 'select' | 'open' | 'close' = 'select',
    ): void {
        if (!this.enabled || !this.context || !this.effectsGain) {
            return;
        }

        const oscillator = this.context.createOscillator();
        const gain = this.context.createGain();
        const now = this.context.currentTime;
        const frequency = { select: 185, open: 146, close: 110 }[kind];

        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(frequency, now);
        oscillator.frequency.exponentialRampToValueAtTime(
            frequency * 1.45,
            now + 0.08,
        );
        gain.gain.setValueAtTime(0.0001, now);
        gain.gain.exponentialRampToValueAtTime(0.08, now + 0.012);
        gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.12);
        oscillator.connect(gain).connect(this.effectsGain);
        oscillator.start(now);
        oscillator.stop(now + 0.13);
    }

    public pause(): void {
        void this.context?.suspend();
    }

    public resume(): void {
        if (this.enabled) {
            void this.context?.resume();
        }
    }

    private startAtmosphere(): void {
        if (!this.context || this.drone || this.wind) {
            return;
        }

        this.ambientGain = this.context.createGain();
        this.ambientGain.gain.value = this.ambientVolume;
        this.ambientGain.connect(this.context.destination);

        this.effectsGain = this.context.createGain();
        this.effectsGain.gain.value = this.effectsVolume;
        this.effectsGain.connect(this.context.destination);

        const lowPass = this.context.createBiquadFilter();
        lowPass.type = 'lowpass';
        lowPass.frequency.value = 180;
        lowPass.Q.value = 0.7;
        lowPass.connect(this.ambientGain);

        this.drone = this.context.createOscillator();
        this.drone.type = 'sine';
        this.drone.frequency.value = 48;
        const droneGain = this.context.createGain();
        droneGain.gain.value = 0.11;
        this.drone.connect(droneGain).connect(lowPass);
        this.drone.start();

        const buffer = this.context.createBuffer(
            1,
            this.context.sampleRate * 2,
            this.context.sampleRate,
        );
        const channel = buffer.getChannelData(0);

        for (let index = 0; index < channel.length; index += 1) {
            channel[index] = (Math.random() * 2 - 1) * 0.16;
        }

        this.wind = this.context.createBufferSource();
        this.wind.buffer = buffer;
        this.wind.loop = true;
        const windFilter = this.context.createBiquadFilter();
        windFilter.type = 'bandpass';
        windFilter.frequency.value = 420;
        windFilter.Q.value = 0.35;
        this.wind.connect(windFilter).connect(this.ambientGain);
        this.wind.start();
    }
}

export const experienceAudio = new ExperienceAudioController();
