import { useEffect } from 'react';
import { useExperience } from './experience-context';

export function PublicPageChoreography() {
    const { visualMode } = useExperience();

    useEffect(() => {
        if (visualMode === 'reduced') {
            return;
        }

        let cancelled = false;
        let cleanup = (): void => undefined;
        const fallbackTimers: number[] = [];

        void Promise.all([import('gsap'), import('gsap/ScrollTrigger')]).then(
            ([{ gsap }, { ScrollTrigger }]) => {
                if (cancelled) {
                    return;
                }

                gsap.registerPlugin(ScrollTrigger);
                const context = gsap.context(() => {
                    const criticalRevealTargets = [
                        '.immersive-page-intro [data-page-kicker]',
                        '.immersive-page-title > span',
                        '.immersive-page-description',
                        '.immersive-page-glyph > *',
                    ];
                    const entrance = gsap.timeline({
                        defaults: { ease: 'power4.out' },
                    });

                    entrance
                        .from('.immersive-page-intro [data-page-kicker]', {
                            opacity: 0,
                            x: -50,
                            duration: 0.9,
                        })
                        .from(
                            '.immersive-page-title > span',
                            {
                                opacity: 0,
                                yPercent: 125,
                                rotateX: -34,
                                filter: 'blur(14px)',
                                duration: 1.2,
                                stagger: 0.1,
                            },
                            0.08,
                        )
                        .from(
                            '.immersive-page-description',
                            {
                                opacity: 0,
                                y: 55,
                                filter: 'blur(9px)',
                                duration: 1,
                            },
                            0.28,
                        )
                        .from(
                            '.immersive-page-glyph > *',
                            {
                                opacity: 0,
                                scale: 0.55,
                                rotate: 26,
                                duration: 1.25,
                                stagger: 0.08,
                            },
                            0.18,
                        );

                    gsap.to('.immersive-page-glyph', {
                        yPercent: 34,
                        rotate: 22,
                        ease: 'none',
                        scrollTrigger: {
                            trigger: '.immersive-page-intro',
                            start: 'top top',
                            end: 'bottom top',
                            scrub: 0.8,
                        },
                    });

                    gsap.to(
                        '.public-immersive-orb-one, .public-immersive-orb-two',
                        {
                            yPercent: 22,
                            scale: 1.06,
                            ease: 'none',
                            scrollTrigger: {
                                trigger: '#main-content',
                                start: 'top top',
                                end: 'bottom bottom',
                                scrub: 1.2,
                            },
                        },
                    );

                    const revealFallback = window.setTimeout(() => {
                        gsap.set(criticalRevealTargets, {
                            clearProps:
                                'opacity,transform,filter,clipPath,visibility',
                        });
                    }, 3200);
                    fallbackTimers.push(revealFallback);

                    entrance.eventCallback('onComplete', () => {
                        window.clearTimeout(revealFallback);
                        gsap.set(criticalRevealTargets, {
                            clearProps:
                                'opacity,transform,filter,clipPath,visibility',
                        });
                    });

                    gsap.to('.immersive-page-coordinate', {
                        xPercent: -18,
                        ease: 'none',
                        scrollTrigger: {
                            trigger: '.immersive-page-intro',
                            start: 'top top',
                            end: 'bottom top',
                            scrub: 0.8,
                        },
                    });

                    document
                        .querySelectorAll<HTMLElement>(
                            '[data-immersive-section]',
                        )
                        .forEach((section) => {
                            const items = section.querySelectorAll(
                                'h2, h3, p, li, a, button',
                            );

                            gsap.from(section, {
                                opacity: 0,
                                y: 110,
                                rotateX: 8,
                                transformOrigin: 'center top',
                                scrollTrigger: {
                                    trigger: section,
                                    start: 'top 88%',
                                    end: 'top 45%',
                                    scrub: 0.7,
                                },
                            });

                            gsap.from(items, {
                                y: 34,
                                stagger: 0.045,
                                ease: 'power3.out',
                                scrollTrigger: {
                                    trigger: section,
                                    start: 'top 72%',
                                    once: true,
                                },
                            });
                        });
                });

                cleanup = () => {
                    fallbackTimers.forEach((timer) =>
                        window.clearTimeout(timer),
                    );
                    context.revert();
                };
            },
        );

        return () => {
            cancelled = true;
            cleanup();
        };
    }, [visualMode]);

    return null;
}
