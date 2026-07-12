import { useEffect, useRef } from 'react';
import type { ComponentPropsWithoutRef } from 'react';
import { cn } from '@/lib/utils';

export function ScrollChapter({
    className,
    ...props
}: ComponentPropsWithoutRef<'section'>) {
    const ref = useRef<HTMLElement>(null);

    useEffect(() => {
        const element = ref.current;

        if (!element || !('IntersectionObserver' in window)) {
            element?.setAttribute('data-visible', 'true');

            return;
        }

        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    element.setAttribute('data-visible', 'true');
                    observer.disconnect();
                }
            },
            { rootMargin: '0px 0px -12% 0px', threshold: 0.08 },
        );

        observer.observe(element);

        return () => observer.disconnect();
    }, []);

    return (
        <section
            ref={ref}
            data-reveal="true"
            className={cn('public-chapter', className)}
            {...props}
        />
    );
}
