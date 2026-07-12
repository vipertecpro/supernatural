import { Link, usePage } from '@inertiajs/react';
import { ExternalLink } from 'lucide-react';
import { BrandWordmark } from '@/components/brand/brand-wordmark';
import { isNavigationActive } from '@/lib/shell/navigation';
import {
    about,
    accessibility,
    contentPolicy,
    copyrightAndTakedown,
    dashboard,
    home,
    login,
    openSource,
    register,
} from '@/routes';
import type { PublicPageProps } from '@/types';

const productLinks = [
    ['Home', home()],
    ['About', about()],
    ['Open Source', openSource()],
] as const;

const trustLinks = [
    ['Accessibility', accessibility()],
    ['Content Policy', contentPolicy()],
    ['Copyright and Takedown', copyrightAndTakedown()],
] as const;

export function PublicFooter() {
    const { auth, publicSite } = usePage<PublicPageProps>().props;
    const currentUrl = usePage().url;

    return (
        <footer className="public-footer border-t border-border-subtle">
            <div className="mx-auto grid max-w-(--content-wide) gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1.4fr_repeat(3,1fr)] lg:py-16">
                <div className="max-w-sm">
                    <BrandWordmark />
                    <p className="mt-5 text-sm leading-6 text-foreground-secondary">
                        An unofficial fan-made companion for Supernatural lore,
                        episodes, hunts, creatures, private journeys, and safer
                        community knowledge.
                    </p>
                    <p className="mt-4 text-xs leading-5 text-foreground-muted">
                        Not affiliated with or endorsed by Warner Bros.
                        Television, The CW, the series creators, cast, or other
                        rights holders.
                    </p>
                </div>
                <FooterGroup
                    title="Product"
                    links={productLinks}
                    currentUrl={currentUrl}
                />
                <FooterGroup
                    title="Trust"
                    links={trustLinks}
                    currentUrl={currentUrl}
                />
                <div>
                    <h2 className="text-label">Account & project</h2>
                    <ul className="mt-4 grid gap-3 text-sm text-foreground-secondary">
                        {auth.user ? (
                            <li>
                                <Link
                                    className="public-text-link"
                                    href={dashboard()}
                                    prefetch
                                >
                                    Dashboard
                                </Link>
                            </li>
                        ) : (
                            <>
                                <li>
                                    <Link
                                        className="public-text-link"
                                        href={login()}
                                        prefetch
                                    >
                                        Sign in
                                    </Link>
                                </li>
                                {publicSite?.registrationAvailable && (
                                    <li>
                                        <Link
                                            className="public-text-link"
                                            href={register()}
                                            prefetch
                                        >
                                            Create account
                                        </Link>
                                    </li>
                                )}
                            </>
                        )}
                        {publicSite?.repositoryUrl && (
                            <li>
                                <a
                                    className="public-text-link inline-flex items-center gap-1.5"
                                    href={publicSite.repositoryUrl}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    Repository{' '}
                                    <ExternalLink
                                        className="size-3.5"
                                        aria-hidden="true"
                                    />
                                </a>
                            </li>
                        )}
                        <li>
                            <span>Active development</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div className="border-t border-border-subtle">
                <div className="mx-auto flex max-w-(--content-wide) flex-col gap-2 px-4 py-5 text-xs text-foreground-muted sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <p>
                        © {publicSite.currentYear} Project-authored website
                        content.
                    </p>
                    <p>
                        The Archive is a working codename. Software license
                        selection remains unresolved.
                    </p>
                </div>
            </div>
        </footer>
    );
}

function FooterGroup({
    title,
    links,
    currentUrl,
}: {
    title: string;
    links: ReadonlyArray<readonly [string, ReturnType<typeof home>]>;
    currentUrl: string;
}) {
    return (
        <div>
            <h2 className="text-label">{title}</h2>
            <ul className="mt-4 grid gap-3 text-sm text-foreground-secondary">
                {links.map(([label, href]) => (
                    <li key={label}>
                        <Link
                            className="public-text-link"
                            href={href}
                            prefetch
                            aria-current={
                                isNavigationActive(currentUrl, href)
                                    ? 'page'
                                    : undefined
                            }
                        >
                            {label}
                        </Link>
                    </li>
                ))}
            </ul>
        </div>
    );
}
