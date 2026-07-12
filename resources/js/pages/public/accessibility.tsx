import { PublicHead } from '@/components/public/public-head';
import {
    PublicArticleSection,
    PublicPageIntro,
} from '@/components/public/public-page-intro';
import type { PublicPageProps } from '@/types';

export default function Accessibility({ publicSite }: PublicPageProps) {
    return (
        <>
            <PublicHead publicSite={publicSite} />
            <PublicPageIntro
                eyebrow="ACCESSIBILITY / STATEMENT"
                title="The archive should open for everyone."
                description="We design and implement toward WCAG 2.2 Level AA. This statement describes an engineering target and current practice; it is not a certification or claim of complete conformance."
                variant="signal"
            />
            <PublicArticleSection title="Current principles">
                <ul>
                    <li>
                        Semantic landmarks, one primary heading, logical heading
                        order, and visible skip links.
                    </li>
                    <li>
                        Keyboard-operable navigation, forms, dialogs, menus, and
                        effects controls with visible focus.
                    </li>
                    <li>
                        Responsive reflow at 320 CSS pixels and layouts intended
                        to remain useful at 200% text zoom.
                    </li>
                    <li>
                        Semantic colour tokens, non-colour state labels,
                        readable contrast targets, and forced-colour fallbacks.
                    </li>
                    <li>
                        Reduced-motion and Data Saver signals automatically
                        replace ambient effects with static, lightweight
                        fallbacks.
                    </li>
                    <li>
                        Decorative SVG and CSS scenes are hidden from assistive
                        technology; essential text never lives only inside an
                        image or effect.
                    </li>
                </ul>
            </PublicArticleSection>
            <PublicArticleSection title="Graphs, timelines, and immersive views">
                <p>
                    Future visual graphs, timelines, and 3D experiences must
                    remain supplemental. The same facts and navigation will be
                    available through structured lists, tables, ordered
                    sequences, labels, and static fallbacks. No essential task
                    will require Canvas, WebGL, animation, or a precision
                    pointer.
                </p>
            </PublicArticleSection>
            <PublicArticleSection title="Known limitations">
                <p>
                    Formal screen-reader coverage, multi-browser
                    assistive-technology testing, 200% zoom review across every
                    route, long-translation review, and forced-colour
                    verification remain ongoing manual gates. Future domain
                    screens may introduce new limitations and must be assessed
                    when implemented.
                </p>
            </PublicArticleSection>
            <PublicArticleSection title="Report an accessibility issue">
                <p>
                    If a configured public repository is available, use its
                    issue process without including private account information,
                    security details, or sensitive content. Security
                    vulnerabilities belong in the repository’s private
                    vulnerability-reporting flow. No personal email address is
                    published as an accessibility channel at this stage.
                </p>
                {publicSite.repositoryUrl && (
                    <p>
                        <a
                            href={publicSite.repositoryUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Open the configured repository
                        </a>
                        .
                    </p>
                )}
            </PublicArticleSection>
        </>
    );
}
