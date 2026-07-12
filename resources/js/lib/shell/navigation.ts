import { Code2, Home, Info, Settings } from 'lucide-react';
import { about, dashboard, home, openSource } from '@/routes';
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
    {
        title: 'About',
        href: about(),
        icon: Info,
        section: 'primary',
        mobilePriority: 2,
    },
    {
        title: 'Open Source',
        href: openSource(),
        icon: Code2,
        section: 'primary',
        mobilePriority: 3,
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
    target: NavItem['href'],
): boolean => {
    const targetUrl = typeof target === 'string' ? target : target.url;

    return (
        currentUrl === targetUrl ||
        (targetUrl !== '/' && currentUrl.startsWith(`${targetUrl}/`))
    );
};
