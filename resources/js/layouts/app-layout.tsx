import FanLayout from '@/layouts/fan/fan-layout';
import type { BreadcrumbItem } from '@/types';

export default function AppLayout({
    breadcrumbs = [],
    children,
}: {
    breadcrumbs?: BreadcrumbItem[];
    children: React.ReactNode;
}) {
    return <FanLayout breadcrumbs={breadcrumbs}>{children}</FanLayout>;
}
