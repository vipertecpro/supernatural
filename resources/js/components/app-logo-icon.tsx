import type { SVGAttributes } from 'react';
import { BrandMark } from '@/components/brand/brand-mark';

export default function AppLogoIcon(props: SVGAttributes<SVGSVGElement>) {
    return <BrandMark decorative {...props} />;
}
