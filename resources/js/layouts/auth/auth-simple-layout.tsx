import { Link, usePage } from '@inertiajs/react';
import { BrandWordmark } from '@/components/brand/brand-wordmark';
import { AppearanceMenu } from '@/components/navigation/appearance-menu';
import { PublicImmersiveBackdrop } from '@/features/experience/public-immersive-backdrop';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    const currentUrl = usePage().url;

    return (
        <div className="immersive-auth-shell relative min-h-svh overflow-hidden bg-(--background-public)">
            <a href="#auth-content" className="skip-link">
                Skip to form
            </a>
            <PublicImmersiveBackdrop url={currentUrl} />
            <div className="absolute top-4 right-4 z-20">
                <AppearanceMenu />
            </div>
            <aside className="immersive-auth-story" aria-hidden="true">
                <p>IDENTITY / SECURE CHANNEL</p>
                <h2>
                    Every archive <span>begins with a witness.</span>
                </h2>
                <div className="immersive-auth-sigil">
                    <span />
                    <span />
                    <span />
                </div>
                <small>ENCRYPTED SESSION · PRIVATE BY DEFAULT</small>
            </aside>
            <main
                id="auth-content"
                className="immersive-auth-panel w-full max-w-md border border-border-strong bg-surface-primary/92 p-6 shadow-surface backdrop-blur-xl sm:p-8"
            >
                <Link href={home()} className="inline-flex">
                    <BrandWordmark />
                </Link>
                <header className="mt-8">
                    <h1 className="text-page-title">{title}</h1>
                    {description && (
                        <p className="text-body-sm mt-2 text-foreground-secondary">
                            {description}
                        </p>
                    )}
                </header>
                <div className="mt-7">{children}</div>
            </main>
        </div>
    );
}
