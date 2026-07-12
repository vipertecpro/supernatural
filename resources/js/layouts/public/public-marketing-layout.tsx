import { Link, usePage } from '@inertiajs/react';
import { Menu } from 'lucide-react';
import type { ReactNode } from 'react';
import { BrandWordmark } from '@/components/brand/brand-wordmark';
import { AppearanceMenu } from '@/components/navigation/appearance-menu';
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
import { dashboard, home, login, register } from '@/routes';

export default function PublicMarketingLayout({
    children,
    hero,
}: {
    children: ReactNode;
    hero?: ReactNode;
}) {
    const { auth } = usePage().props;

    return (
        <div className="min-h-svh bg-(--background-public)">
            <a href="#main-content" className="skip-link">
                Skip to content
            </a>
            <OfflineBanner />
            <header className="sticky top-0 border-b border-border-subtle bg-background/92 backdrop-blur">
                <div className="mx-auto flex h-(--shell-header-height) max-w-(--content-wide) items-center gap-4 px-4 sm:px-6">
                    <Link href={home()} aria-label="The Archive home">
                        <BrandWordmark />
                    </Link>
                    <nav
                        aria-label="Public navigation"
                        className="ml-8 hidden items-center gap-1 md:flex"
                    >
                        <Button variant="ghost" asChild>
                            <Link href={home()} aria-current="page">
                                Home
                            </Link>
                        </Button>
                    </nav>
                    <div className="ml-auto flex items-center gap-1">
                        <AppearanceMenu />
                        <div className="hidden items-center gap-2 sm:flex">
                            {auth.user ? (
                                <Button asChild>
                                    <Link href={dashboard()}>Open app</Link>
                                </Button>
                            ) : (
                                <>
                                    <Button variant="ghost" asChild>
                                        <Link href={login()}>Sign in</Link>
                                    </Button>
                                    <Button asChild>
                                        <Link href={register()}>
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
                                className="flex w-full max-w-sm flex-col"
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
                                    <SheetClose asChild>
                                        <Button
                                            variant="ghost"
                                            className="justify-start"
                                            asChild
                                        >
                                            <Link href={home()}>Home</Link>
                                        </Button>
                                    </SheetClose>
                                    <div className="mt-auto flex flex-col gap-2">
                                        {auth.user ? (
                                            <Button asChild>
                                                <Link href={dashboard()}>
                                                    Open app
                                                </Link>
                                            </Button>
                                        ) : (
                                            <>
                                                <Button
                                                    variant="outline"
                                                    asChild
                                                >
                                                    <Link href={login()}>
                                                        Sign in
                                                    </Link>
                                                </Button>
                                                <Button asChild>
                                                    <Link href={register()}>
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
            <main id="main-content" tabIndex={-1}>
                {children}
            </main>
            <footer className="border-t border-border-subtle">
                <div className="mx-auto flex max-w-(--content-wide) flex-col gap-2 px-4 py-8 text-sm text-foreground-muted sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <p>The Archive is a working codename.</p>
                    <p>Original, fandom-neutral product foundation.</p>
                </div>
            </footer>
        </div>
    );
}
