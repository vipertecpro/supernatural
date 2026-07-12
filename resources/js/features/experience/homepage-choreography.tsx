import { useEffect } from 'react';
import { useExperience } from './experience-context';

export function HomepageChoreography() {
    const { visualMode } = useExperience();

    useEffect(() => {
        if (visualMode === 'reduced') {
            return;
        }

        let cleanup = (): void => undefined;
        let cancelled = false;
        const fallbackTimers: number[] = [];

        void Promise.all([import('gsap'), import('gsap/ScrollTrigger')]).then(
            ([{ gsap }, { ScrollTrigger }]) => {
                if (cancelled) {
                    return;
                }

                gsap.registerPlugin(ScrollTrigger);
                const context = gsap.context(() => {
                    const heroRevealTargets = [
                        '.archive-hero [data-hero-reveal]',
                        '.archive-hero-title > span',
                    ];
                    const heroTimeline = gsap.timeline({
                        defaults: { ease: 'power4.out' },
                    });

                    heroTimeline.from('.archive-hero [data-hero-reveal]', {
                        opacity: 0,
                        y: 78,
                        filter: 'blur(14px)',
                        duration: 1.35,
                        stagger: 0.16,
                    });
                    heroTimeline.from(
                        '.archive-hero-title > span',
                        {
                            yPercent: 120,
                            rotateX: -32,
                            filter: 'blur(18px)',
                            clipPath: 'inset(100% 0 0 0)',
                            duration: 1.25,
                            stagger: 0.14,
                        },
                        0.12,
                    );

                    const heroRevealFallback = window.setTimeout(() => {
                        gsap.set(heroRevealTargets, {
                            clearProps:
                                'opacity,transform,filter,clipPath,visibility',
                        });
                    }, 3600);
                    fallbackTimers.push(heroRevealFallback);

                    heroTimeline.eventCallback('onComplete', () => {
                        window.clearTimeout(heroRevealFallback);
                        gsap.set(heroRevealTargets, {
                            clearProps:
                                'opacity,transform,filter,clipPath,visibility',
                        });
                    });

                    gsap.to('.archive-hero-aurora-one', {
                        xPercent: 22,
                        yPercent: 12,
                        rotate: 18,
                        duration: 8,
                        repeat: -1,
                        yoyo: true,
                        ease: 'sine.inOut',
                    });
                    gsap.to('.archive-hero-aurora-two', {
                        xPercent: -18,
                        yPercent: -14,
                        rotate: -14,
                        duration: 10,
                        repeat: -1,
                        yoyo: true,
                        ease: 'sine.inOut',
                    });

                    document
                        .querySelectorAll<HTMLElement>('.public-chapter')
                        .forEach((chapter) => {
                            const copy = chapter.querySelector(
                                '.public-chapter-copy',
                            );
                            const visual = chapter.querySelector(
                                '.public-chapter-visual',
                            );
                            const previewItems = visual?.querySelectorAll(
                                '.record-sheet, .journey-node, .evidence-graph > span, .spoiler-preview > div, .bunker-ring, .bunker-dot, .source-preview > div',
                            );

                            if (copy) {
                                gsap.fromTo(
                                    copy,
                                    {
                                        opacity: 0.08,
                                        y: 140,
                                        filter: 'blur(12px)',
                                    },
                                    {
                                        opacity: 1,
                                        y: -24,
                                        filter: 'blur(0px)',
                                        ease: 'none',
                                        scrollTrigger: {
                                            trigger: chapter,
                                            start: 'top 92%',
                                            end: 'top 28%',
                                            scrub: 1,
                                        },
                                    },
                                );
                            }

                            if (visual) {
                                gsap.fromTo(
                                    visual,
                                    {
                                        opacity: 0.12,
                                        scale: 0.68,
                                        rotateY:
                                            chapter.dataset.sceneIndex === '2'
                                                ? 16
                                                : -16,
                                        rotateX: 10,
                                    },
                                    {
                                        opacity: 1,
                                        scale: 1.04,
                                        rotateY: 0,
                                        rotateX: 0,
                                        ease: 'none',
                                        scrollTrigger: {
                                            trigger: chapter,
                                            start: 'top 92%',
                                            end: 'bottom 32%',
                                            scrub: 1.1,
                                        },
                                    },
                                );
                            }

                            if (previewItems && previewItems.length > 0) {
                                gsap.from(previewItems, {
                                    opacity: 0,
                                    y: 90,
                                    rotate: 9,
                                    scale: 0.72,
                                    stagger: 0.08,
                                    ease: 'power3.out',
                                    scrollTrigger: {
                                        trigger: chapter,
                                        start: 'top 62%',
                                        once: true,
                                    },
                                });
                            }

                            gsap.fromTo(
                                chapter,
                                { '--scene-progress': 0 },
                                {
                                    '--scene-progress': 1,
                                    ease: 'none',
                                    scrollTrigger: {
                                        trigger: chapter,
                                        start: 'top bottom',
                                        end: 'bottom top',
                                        scrub: true,
                                    },
                                },
                            );
                        });

                    gsap.to('.archive-hero-scene', {
                        yPercent: 28,
                        scale: 1.14,
                        filter: 'brightness(0.58) blur(2px)',
                        ease: 'none',
                        scrollTrigger: {
                            trigger: '.archive-hero',
                            start: 'top top',
                            end: 'bottom top',
                            scrub: 0.7,
                        },
                    });

                    gsap.to('.archive-hero-ghost-title', {
                        xPercent: -24,
                        opacity: 0,
                        ease: 'none',
                        scrollTrigger: {
                            trigger: '.archive-hero',
                            start: 'top top',
                            end: 'bottom top',
                            scrub: 0.8,
                        },
                    });

                    gsap.from('.media-reel img', {
                        y: 150,
                        scale: 0.74,
                        rotate: 4,
                        opacity: 0,
                        stagger: 0.09,
                        ease: 'power4.out',
                        scrollTrigger: {
                            trigger: '.media-interlude',
                            start: 'top 72%',
                            end: 'top 20%',
                            scrub: 1,
                        },
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
