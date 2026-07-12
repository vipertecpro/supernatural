import { Home, Settings } from 'lucide-react';
import { dashboard, home } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import type { NavItem } from '@/types';

export type ShellNavigationItem = NavItem & {
    section: 'primary' | 'utility';
    mobilePriority?: number;
    requiresAuth?: boolean;
    requiresVerification?: boolean;
};
export const publicNavigation: ShellNavigationItem[] = [
    {
        title: 'Home',
        href: home(),
        icon: Home,
        section: 'primary',
        mobilePriority: 1,
    },
];
export const fanNavigation: ShellNavigationItem[] = [
    {
        title: 'Home',
        href: dashboard(),
        icon: Home,
        section: 'primary',
        mobilePriority: 1,
        requiresAuth: true,
        requiresVerification: true,
    },
    {
        title: 'Settings',
        href: editProfile(),
        icon: Settings,
        section: 'utility',
        mobilePriority: 2,
        requiresAuth: true,
        requiresVerification: true,
    },
];
export const isNavigationActive = (
    currentUrl: string,
    targetUrl: string,
): boolean =>
    currentUrl === targetUrl ||
    (targetUrl !== '/' && currentUrl.startsWith(`${targetUrl}/`));
