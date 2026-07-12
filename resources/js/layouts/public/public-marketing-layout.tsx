import { Link, usePage } from '@inertiajs/react';
import { Menu } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { ReactNode } from 'react';
import { BrandWordmark } from '@/components/brand/brand-wordmark';
import { AppearanceMenu } from '@/components/navigation/appearance-menu';
import { PublicFooter } from '@/components/public/public-footer';
import { OfflineBanner } from '@/components/states/offline-banner';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetClose,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { useExperience } from '@/features/experience/experience-context';
import { PublicImmersiveBackdrop } from '@/features/experience/public-immersive-backdrop';
import { isNavigationActive, publicNavigation } from '@/lib/shell/navigation';
import { dashboard, home, login, register } from '@/routes';

export default function PublicMarketingLayout({
    children,
    hero,
}: {
    children: ReactNode;
    hero?: ReactNode;
}) {
    const { auth } = usePage().props;
    const currentUrl = usePage().url;
    const { visualMode } = useExperience();
    const [scrolled, setScrolled] = useState(false);

    useEffect(() => {
        const update = (): void => setScrolled(window.scrollY > 24);
        update();
        window.addEventListener('scroll', update, { passive: true });

        return () => window.removeEventListener('scroll', update);
    }, []);

    return (
        <div
            data-experience-surface="public"
            data-public-route={currentUrl.split('?')[0] || '/'}
            className="min-h-svh overflow-x-clip bg-(--background-public)"
        >
            <a href="#main-content" className="skip-link">
                Skip to content
            </a>
            <OfflineBanner />
            <PublicImmersiveBackdrop url={currentUrl} />
            {hero && (
                <a href="#archive-opens" className="skip-link skip-intro-link">
                    Skip introduction
                </a>
            )}
            <header
                className="public-header sticky top-0 z-40"
                data-scrolled={scrolled}
            >
                <div className="public-header-inner mx-auto flex h-(--shell-header-height) max-w-(--content-wide) items-center gap-4 px-4 sm:px-6">
                    <Link
                        href={home()}
                        prefetch
                        aria-label="The Archive home"
                        viewTransition={visualMode !== 'reduced'}
                    >
                        <BrandWordmark />
                    </Link>
                    <nav
                        aria-label="Public navigation"
                        className="ml-8 hidden items-center gap-1 md:flex"
                    >
                        {publicNavigation.map(({ title, href }) => (
                            <Button key={title} variant="ghost" asChild>
                                <Link
                                    href={href}
                                    prefetch
                                    viewTransition={visualMode !== 'reduced'}
                                    aria-current={
                                        isNavigationActive(currentUrl, href)
                                            ? 'page'
                                            : undefined
                                    }
                                >
                                    {title}
                                </Link>
                            </Button>
                        ))}
                    </nav>
                    <div className="ml-auto flex items-center gap-1">
                        <AppearanceMenu />
                        <div className="hidden items-center gap-2 sm:flex">
                            {auth.user ? (
                                <Button asChild>
                                    <Link href={dashboard()} prefetch>
                                        Open app
                                    </Link>
                                </Button>
                            ) : (
                                <>
                                    <Button variant="ghost" asChild>
                                        <Link href={login()} prefetch>
                                            Sign in
                                        </Link>
                                    </Button>
                                    <Button asChild>
                                        <Link href={register()} prefetch>
                                            Create account
                                        </Link>
                                    </Button>
                                </>
                            )}
                        </div>
                        <Sheet>
                            <SheetTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    className="md:hidden"
                                    aria-label="Open navigation"
                                >
                                    <Menu />
                                </Button>
                            </SheetTrigger>
                            <SheetContent
                                side="right"
                                className="public-mobile-menu flex w-full max-w-none flex-col md:max-w-sm"
                            >
                                <SheetHeader>
                                    <SheetTitle>Navigation</SheetTitle>
                                    <SheetDescription>
                                        Move through currently available
                                        destinations.
                                    </SheetDescription>
                                </SheetHeader>
                                <nav
                                    aria-label="Mobile public navigation"
                                    className="flex flex-1 flex-col gap-2 p-4"
                                >
                                    {publicNavigation.map(({ title, href }) => (
                                        <SheetClose key={title} asChild>
                                            <Button
                                                variant="ghost"
                                                className="h-11 justify-start"
                                                asChild
                                            >
                                                <Link
                                                    href={href}
                                                    prefetch
                                                    viewTransition={
                                                        visualMode !== 'reduced'
                                                    }
                                                    aria-current={
                                                        isNavigationActive(
                                                            currentUrl,
                                                            href,
                                                        )
                                                            ? 'page'
                                                            : undefined
                                                    }
                                                >
                                                    {title}
                                                </Link>
                                            </Button>
                                        </SheetClose>
                                    ))}
                                    <div className="mt-auto flex flex-col gap-2">
                                        {auth.user ? (
                                            <Button asChild>
                                                <Link
                                                    href={dashboard()}
                                                    prefetch
                                                >
                                                    Open app
                                                </Link>
                                            </Button>
                                        ) : (
                                            <>
                                                <Button
                                                    variant="outline"
                                                    asChild
                                                >
                                                    <Link
                                                        href={login()}
                                                        prefetch
                                                    >
                                                        Sign in
                                                    </Link>
                                                </Button>
                                                <Button asChild>
                                                    <Link
                                                        href={register()}
                                                        prefetch
                                                    >
                                                        Create account
                                                    </Link>
                                                </Button>
                                            </>
                                        )}
                                    </div>
                                </nav>
                            </SheetContent>
                        </Sheet>
                    </div>
                </div>
            </header>
            {hero}
            <main
                id="main-content"
                tabIndex={-1}
                className="public-main-content"
            >
                {children}
            </main>
            <PublicFooter />
        </div>
    );
}
