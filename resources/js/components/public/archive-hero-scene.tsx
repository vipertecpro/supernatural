import { lazy, Suspense, useEffect, useRef, useState } from 'react';
import { useExperience } from '@/features/experience/experience-context';
import { SceneErrorBoundary } from '@/features/experience/scene-error-boundary';

const NightRoadScene = lazy(
    () => import('@/features/experience/night-road-scene'),
);

export function ArchiveHeroScene() {
    const ref = useRef<HTMLDivElement>(null);
    const [active, setActive] = useState(true);
    const { quality, webglEnabled, reportWebglFailure } = useExperience();

    useEffect(() => {
        const element = ref.current;

        if (!element || !('IntersectionObserver' in window)) {
            element?.setAttribute('data-active', 'true');

            return;
        }

        const observer = new IntersectionObserver(([entry]) => {
            element.dataset.active = entry.isIntersecting ? 'true' : 'false';
            setActive(entry.isIntersecting);
        });
        observer.observe(element);

        return () => observer.disconnect();
    }, []);

    return (
        <div ref={ref} className="archive-hero-scene" aria-hidden="true">
            {webglEnabled && (
                <SceneErrorBoundary
                    fallback={null}
                    onError={reportWebglFailure}
                >
                    <Suspense fallback={null}>
                        <div className="archive-hero-canvas">
                            <NightRoadScene
                                active={active}
                                quality={quality}
                                onContextLost={reportWebglFailure}
                            />
                        </div>
                    </Suspense>
                </SceneErrorBoundary>
            )}
            <div className="archive-hero-vignette" />
            <div className="archive-hero-aurora archive-hero-aurora-one" />
            <div className="archive-hero-aurora archive-hero-aurora-two" />
            <div className="archive-hero-light-sweep" />
            <div className="archive-hero-scanlines" />
            <div className="archive-hero-ghost-title">
                <span>THE</span>
                <span>ARCHIVE</span>
                <span>IS OPEN</span>
            </div>
            <div className="archive-hero-glow" />
            <div className="archive-hero-grid" />
            <div className="archive-hero-road">
                <span />
                <span />
                <span />
            </div>
            <svg className="archive-signal" viewBox="0 0 640 280" fill="none">
                <path d="M12 222C84 216 112 178 170 186s79 49 139 20c49-24 75-98 143-82 51 12 77 83 176 48" />
                {[48, 170, 309, 452, 588].map((x, index) => (
                    <circle
                        key={x}
                        cx={x}
                        cy={[216, 186, 206, 124, 172][index]}
                        r="4"
                    />
                ))}
            </svg>
            <div className="archive-document archive-document-back">
                <span className="archive-document-label">ARCHIVE / SIGNAL</span>
            </div>
            <div className="archive-document archive-document-front">
                <span className="archive-document-mark" />
                <span className="archive-document-line" />
                <span className="archive-document-line archive-document-line-short" />
            </div>
            <div className="archive-hero-reticle">
                <span />
                <span />
                <span />
            </div>
            <div className="film-grain" />
        </div>
    );
}
