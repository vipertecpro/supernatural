import { Link } from '@inertiajs/react';
import { BrandWordmark } from '@/components/brand/brand-wordmark';
import { AppearanceMenu } from '@/components/navigation/appearance-menu';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="archive-atmosphere relative flex min-h-svh items-center justify-center bg-(--background-public) px-4 py-10 sm:px-6">
            <a href="#auth-content" className="skip-link">
                Skip to form
            </a>
            <div className="absolute top-4 right-4">
                <AppearanceMenu />
            </div>
            <main
                id="auth-content"
                className="w-full max-w-md rounded-xl border border-border-strong bg-surface-primary p-6 shadow-surface sm:p-8"
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
