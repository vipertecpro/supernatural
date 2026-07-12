import { useEffect, useState } from 'react';
import { useExperience } from './experience-context';

export function CinematicPreloader() {
    const { introComplete, completeIntro, visualMode } = useExperience();
    const [progress, setProgress] = useState(0);

    useEffect(() => {
        if (introComplete || visualMode === 'reduced') {
            return;
        }

        let cancelled = false;
        const update = (value: number): void => {
            if (!cancelled) {
                setProgress(value);
            }
        };

        update(document.readyState === 'complete' ? 55 : 25);
        const loaded = (): void => update(65);
        window.addEventListener('load', loaded, { once: true });
        void document.fonts.ready.then(() => update(100));

        return () => {
            cancelled = true;
            window.removeEventListener('load', loaded);
        };
    }, [introComplete, visualMode]);

    useEffect(() => {
        if (progress < 100 || introComplete) {
            return;
        }

        const timeout = window.setTimeout(completeIntro, 550);

        return () => window.clearTimeout(timeout);
    }, [completeIntro, introComplete, progress]);

    if (introComplete || visualMode === 'reduced') {
        return null;
    }

    return (
        <div
            className="cinematic-preloader"
            role="dialog"
            aria-modal="true"
            aria-label="Opening The Archive"
        >
            <div className="cinematic-preloader-signal" aria-hidden="true" />
            <div className="cinematic-preloader-mark" aria-hidden="true">
                <span />
                <span />
                <span />
            </div>
            <p className="text-metadata">RESTRICTED ARCHIVE / ENTRY</p>
            <p className="font-casefile text-2xl">Signal acquired</p>
            <div
                className="cinematic-preloader-progress"
                role="progressbar"
                aria-valuemin={0}
                aria-valuemax={100}
                aria-valuenow={progress}
            >
                <span style={{ width: `${progress}%` }} />
            </div>
            <p className="text-metadata">{progress}%</p>
            <button type="button" onClick={completeIntro}>
                Skip introduction
            </button>
        </div>
    );
}
