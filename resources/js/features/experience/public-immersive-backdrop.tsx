import { lazy, Suspense, useEffect, useRef, useState } from 'react';
import { useExperience } from './experience-context';
import { resolvePublicSceneVariant } from './public-scene-variants';
import { SceneErrorBoundary } from './scene-error-boundary';

const AmbientArchiveScene = lazy(() => import('./ambient-archive-scene'));

export function PublicImmersiveBackdrop({ url }: { url: string }) {
    const container = useRef<HTMLDivElement>(null);
    const [mounted, setMounted] = useState(false);
    const [active, setActive] = useState(true);
    const { quality, webglEnabled, reportWebglFailure } = useExperience();
    const variant = resolvePublicSceneVariant(url);

    useEffect(() => {
        let available = true;
        queueMicrotask(() => {
            if (available) {
                setMounted(true);
            }
        });

        const update = (): void => setActive(!document.hidden);
        document.addEventListener('visibilitychange', update);

        return () => {
            available = false;
            document.removeEventListener('visibilitychange', update);
        };
    }, []);

    return (
        <div
            ref={container}
            className="public-immersive-backdrop"
            data-scene-variant={variant}
            aria-hidden="true"
        >
            <div className="public-immersive-orb public-immersive-orb-one" />
            <div className="public-immersive-orb public-immersive-orb-two" />
            <div className="public-immersive-beam" />
            <div className="public-immersive-noise" />
            {mounted && webglEnabled && (
                <SceneErrorBoundary
                    fallback={null}
                    onError={reportWebglFailure}
                >
                    <Suspense fallback={null}>
                        <div className="public-immersive-canvas">
                            <AmbientArchiveScene
                                active={active}
                                quality={quality}
                                variant={variant}
                                onContextLost={reportWebglFailure}
                            />
                        </div>
                    </Suspense>
                </SceneErrorBoundary>
            )}
        </div>
    );
}
