import { useEffect, useMemo, useRef, useState } from 'react';
import { useExperience } from './experience-context';
import { HeroSoundControl } from './hero-sound-control';
import { resolvePublicCollageImages } from './public-collage-images';
import { resolvePublicSceneVariant } from './public-scene-variants';

export function PublicImmersiveBackdrop({ url }: { url: string }) {
    const container = useRef<HTMLDivElement>(null);
    const [imageCycle, setImageCycle] = useState<{
        current: number;
        previous: number | null;
    }>({ current: 0, previous: null });
    const { current: currentCycle, previous: previousCycle } = imageCycle;
    const { visualMode } = useExperience();
    const variant = resolvePublicSceneVariant(url);
    const routeImages = useMemo(
        () => resolvePublicCollageImages(variant),
        [variant],
    );
    const currentImage = routeImages[currentCycle % routeImages.length];
    const previousImage =
        previousCycle === null
            ? null
            : routeImages[previousCycle % routeImages.length];

    useEffect(() => {
        if (visualMode === 'reduced') {
            return;
        }

        const interval = window.setInterval(
            () =>
                setImageCycle(({ current }) => ({
                    current: current + 1,
                    previous: current,
                })),
            10000,
        );

        return () => window.clearInterval(interval);
    }, [visualMode]);

    useEffect(() => {
        if (visualMode === 'reduced') {
            return;
        }

        const nextImage = routeImages[(currentCycle + 1) % routeImages.length];
        const preload = new Image();
        preload.src = nextImage.src;
    }, [currentCycle, routeImages, visualMode]);

    useEffect(() => {
        const element = container.current;

        if (!element || visualMode === 'reduced') {
            return;
        }

        let frame = 0;
        let pointerX = 0;
        let pointerY = 0;
        const renderDepth = (x: number, y: number, scroll: number): void => {
            cancelAnimationFrame(frame);
            frame = requestAnimationFrame(() => {
                element.style.setProperty(
                    '--scene-shift-x',
                    `${(-x * 1.4).toFixed(3)}vw`,
                );
                element.style.setProperty(
                    '--scene-shift-y',
                    `${(-y * 1.4).toFixed(3)}vh`,
                );
                element.style.setProperty(
                    '--scene-scroll-y',
                    `${(-Math.min(scroll, 2800) * 0.008).toFixed(2)}px`,
                );
            });
        };
        const updatePointer = (event: PointerEvent): void => {
            pointerX = event.clientX / window.innerWidth - 0.5;
            pointerY = event.clientY / window.innerHeight - 0.5;
            renderDepth(pointerX, pointerY, window.scrollY);
        };
        const updateScroll = (): void =>
            renderDepth(pointerX, pointerY, window.scrollY);

        window.addEventListener('pointermove', updatePointer, {
            passive: true,
        });
        window.addEventListener('scroll', updateScroll, { passive: true });

        return () => {
            cancelAnimationFrame(frame);
            window.removeEventListener('pointermove', updatePointer);
            window.removeEventListener('scroll', updateScroll);
        };
    }, [visualMode]);

    return (
        <>
            <div
                ref={container}
                className="public-immersive-backdrop"
                data-scene-variant={variant}
                aria-hidden="true"
            >
                <div className="cinematic-image-stage">
                    <div className="cinematic-image-frame">
                        {previousImage ? (
                            <img
                                className="cinematic-image-previous"
                                src={previousImage.src}
                                alt=""
                                width={previousImage.width}
                                height={previousImage.height}
                                aria-hidden="true"
                                decoding="async"
                                style={{
                                    objectPosition: previousImage.focalPosition,
                                }}
                            />
                        ) : null}
                        <img
                            key={`${currentCycle}-${currentImage.id}`}
                            className="cinematic-image-current"
                            src={currentImage.src}
                            alt=""
                            width={currentImage.width}
                            height={currentImage.height}
                            loading="eager"
                            decoding="async"
                            style={{
                                objectPosition: currentImage.focalPosition,
                            }}
                        />
                    </div>
                </div>
                <div className="cinematic-image-scrim" />
            </div>
            <div className="cinematic-collage-sound">
                <HeroSoundControl />
            </div>
            <details className="cinematic-collage-credits">
                <summary>Image credits</summary>
                <div>
                    <p>{routeImages.length} supernatural background sources</p>
                    <p className="cinematic-collage-motion-credit">
                        Motion direction inspired by{' '}
                        <a
                            href="https://skiper-ui.com/components"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Skiper UI
                        </a>
                    </p>
                    <ul>
                        {routeImages.map((image) => (
                            <li key={image.id}>
                                <a
                                    href={image.sourceUrl}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    {image.title ?? image.id}
                                </a>
                                <span>
                                    {image.artist ? `${image.artist} · ` : null}
                                    {image.license ?? 'User-directed reference'}
                                </span>
                            </li>
                        ))}
                    </ul>
                </div>
            </details>
        </>
    );
}
