import type { SVGAttributes } from 'react';
import { cn } from '@/lib/utils';

type BrandMarkProps = SVGAttributes<SVGSVGElement> & {
    decorative?: boolean;
    label?: string;
};

export function BrandMark({
    decorative = false,
    label = 'The Archive',
    className,
    ...props
}: BrandMarkProps) {
    return (
        <svg
            viewBox="0 0 48 48"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden={decorative || undefined}
            aria-label={decorative ? undefined : label}
            role={decorative ? undefined : 'img'}
            className={cn('shrink-0', className)}
            {...props}
        >
            <path
                d="M10 12.5 24 5l14 7.5v23L24 43 10 35.5v-23Z"
                stroke="currentColor"
                strokeWidth="2.5"
            />
            <path
                d="m16 16 8-4.25L32 16v16l-8 4.25L16 32V16Z"
                stroke="currentColor"
                strokeWidth="2"
            />
            <path
                d="M24 11.75v24.5M16 20h16M16 28h16"
                stroke="currentColor"
                strokeWidth="2"
            />
            <circle cx="24" cy="24" r="2.75" fill="currentColor" />
        </svg>
    );
}
