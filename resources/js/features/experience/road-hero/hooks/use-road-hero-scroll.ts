/* eslint-disable react-hooks/immutability -- ScrollTrigger writes to a non-rendering runtime shared with R3F. */
import { useEffect } from 'react';
import type { MutableRefObject, RefObject } from 'react';
import { experienceAudio } from '../../audio-controller';
import type { RoadHeroRuntime } from '../types';

export function useRoadHeroScroll(
    section: RefObject<HTMLElement | null>,
    runtime: MutableRefObject<RoadHeroRuntime>,
    reduced: boolean,
) {
    useEffect(() => {
        const element = section.current;
        const runtimeState = runtime.current;

        if (!element || reduced) {
            runtimeState.progress = reduced ? 0.18 : 0;

            return;
        }

        let cancelled = false;
        let cleanup = (): void => undefined;

        void Promise.all([import('gsap'), import('gsap/ScrollTrigger')]).then(
            ([{ gsap }, { ScrollTrigger }]) => {
                if (cancelled) {
                    return;
                }

                gsap.registerPlugin(ScrollTrigger);
                const context = gsap.context(() => {
                    ScrollTrigger.create({
                        trigger: element,
                        start: 'top top',
                        end: 'bottom bottom',
                        scrub: 0.65,
                        invalidateOnRefresh: true,
                        onUpdate: ({ progress }) => {
                            const previous = runtimeState.progress;
                            runtimeState.previousProgress = previous;
                            runtimeState.progress = progress;
                            runtimeState.velocity +=
                                (Math.abs(progress - previous) * 80 -
                                    runtimeState.velocity) *
                                0.22;
                            experienceAudio.setMotion(
                                progress,
                                runtimeState.velocity,
                            );
                            element.style.setProperty(
                                '--road-hero-progress',
                                progress.toFixed(4),
                            );
                            element.dataset.chapter =
                                progress < 0.3
                                    ? 'departure'
                                    : progress < 0.65
                                      ? 'approach'
                                      : 'threshold';
                        },
                    });

                    gsap.fromTo(
                        '[data-road-hero-reveal]',
                        { autoAlpha: 0, y: 34, filter: 'blur(12px)' },
                        {
                            autoAlpha: 1,
                            y: 0,
                            filter: 'blur(0px)',
                            duration: 1.35,
                            stagger: 0.12,
                            ease: 'power4.out',
                            clearProps: 'transform,filter,visibility',
                        },
                    );
                }, element);

                cleanup = () => {
                    context.revert();
                    ScrollTrigger.getAll()
                        .filter((trigger) => trigger.trigger === element)
                        .forEach((trigger) => trigger.kill());
                };
            },
        );

        return () => {
            cancelled = true;
            cleanup();
            runtimeState.active = false;
        };
    }, [reduced, runtime, section]);
}
