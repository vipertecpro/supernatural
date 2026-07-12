import { useEffect, useState } from 'react';
import { useExperience } from '../../experience-context';

export function HeroLoader({ sceneReady }: { sceneReady: boolean }) {
    const { introComplete, completeIntro, visualMode } = useExperience();
    const [fontsReady, setFontsReady] = useState(false);

    useEffect(() => {
        let active = true;

        void document.fonts.ready.then(() => {
            if (active) {
                setFontsReady(true);
            }
        });

        return () => {
            active = false;
        };
    }, []);

    const progress = sceneReady
        ? fontsReady
            ? 100
            : 72
        : fontsReady
          ? 58
          : 24;

    useEffect(() => {
        if (progress < 100 || introComplete) {
            return;
        }

        const timeout = window.setTimeout(completeIntro, 320);

        return () => window.clearTimeout(timeout);
    }, [completeIntro, introComplete, progress]);

    if (introComplete || visualMode === 'reduced') {
        return null;
    }

    return (
        <div
            className="road-hero-loader"
            role="dialog"
            aria-modal="true"
            aria-label="Opening The Archive"
        >
            <div className="road-hero-loader-signal" aria-hidden="true" />
            <div className="road-hero-loader-mark" aria-hidden="true">
                <span />
                <span />
                <span />
            </div>
            <p className="font-casefile text-sm tracking-[0.22em] uppercase">
                Archive frequency 67.3
            </p>
            <p className="font-editorial text-3xl">The road remembers.</p>
            <div
                className="road-hero-loader-track"
                role="progressbar"
                aria-valuemin={0}
                aria-valuemax={100}
                aria-valuenow={progress}
            >
                <span style={{ width: `${progress}%` }} />
            </div>
            <div className="flex w-full items-center justify-between gap-6 text-xs tracking-[0.18em] uppercase">
                <span>{progress}%</span>
                <button type="button" onClick={completeIntro}>
                    Skip intro
                </button>
            </div>
        </div>
    );
}
