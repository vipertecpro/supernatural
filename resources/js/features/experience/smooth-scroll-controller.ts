let destroyCurrent: (() => void) | null = null;

export const startSmoothScroll = async (): Promise<(() => void) | null> => {
    if (
        destroyCurrent ||
        !document.querySelector('[data-experience-surface="public"]')
    ) {
        return destroyCurrent;
    }

    const [{ default: Lenis }, { gsap }, { ScrollTrigger }] = await Promise.all(
        [import('lenis'), import('gsap'), import('gsap/ScrollTrigger')],
    );

    gsap.registerPlugin(ScrollTrigger);
    const lenis = new Lenis({
        duration: 1.05,
        smoothWheel: true,
        syncTouch: false,
        anchors: { offset: -72 },
        prevent: (node) =>
            node.closest(
                'form, dialog, [role="dialog"], [data-native-scroll="true"]',
            ) !== null,
    });
    const update = (time: number): void => lenis.raf(time * 1000);
    const refresh = (): void => ScrollTrigger.update();

    lenis.on('scroll', refresh);
    gsap.ticker.add(update);
    gsap.ticker.lagSmoothing(0);

    destroyCurrent = () => {
        lenis.off('scroll', refresh);
        gsap.ticker.remove(update);
        lenis.destroy();
        ScrollTrigger.getAll().forEach((trigger) => trigger.kill());
        destroyCurrent = null;
    };

    return destroyCurrent;
};

export const stopSmoothScroll = (): void => destroyCurrent?.();
