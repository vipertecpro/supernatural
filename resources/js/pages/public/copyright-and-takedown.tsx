import { PublicHead } from '@/components/public/public-head';
import {
    PublicArticleSection,
    PublicPageIntro,
} from '@/components/public/public-page-intro';
import type { PublicPageProps } from '@/types';

export default function CopyrightAndTakedown({ publicSite }: PublicPageProps) {
    return (
        <>
            <PublicHead publicSite={publicSite} />
            <PublicPageIntro
                eyebrow="TRUST / RIGHTS"
                title="Copyright and takedown process."
                description="This page describes the project’s current handling process. It is not legal advice, does not determine ownership, and is not a jurisdiction-specific legal notice policy."
            />
            <aside
                className="public-note"
                aria-label="Unofficial project disclaimer"
            >
                <h2>Unofficial project</h2>
                <p>
                    The project is independent and unaffiliated. Names, stories,
                    characters, brands, and third-party media remain the
                    property of their respective rights holders. No use is
                    represented as automatically fair use.
                </p>
            </aside>
            <PublicArticleSection title="Linking, embedding, and hosting are different">
                <p>
                    A link points to another location. A provider-authorized
                    embed displays media under that provider’s terms and removal
                    controls. Hosting stores or distributes a copy. Permission
                    for one does not grant permission for another.
                </p>
                <ul>
                    <li>
                        Do not upload or rehost episodes, protected video, or
                        promotional footage.
                    </li>
                    <li>
                        Do not upload or rehost licensed soundtrack music or
                        protected audio.
                    </li>
                    <li>
                        Do not copy full transcripts or substantial protected
                        text.
                    </li>
                    <li>
                        Do not submit fan art without the creator’s permission
                        or a licence allowing the intended use and attribution.
                    </li>
                </ul>
            </PublicArticleSection>
            <PublicArticleSection title="Submitting a concern">
                <p>
                    Rights holders or authorized representatives may use a
                    configured repository issue only to state that a concern
                    exists and request private follow-up. Do not place
                    addresses, identification documents, signatures, contracts,
                    or other sensitive information in a public issue.
                </p>
                <p>
                    A complete private notice should identify the protected work
                    or authorized representative, the exact repository or
                    application location, the basis and requested action, a
                    good-faith statement, accurate private contact information,
                    and relevant licence, permission, or countervailing context.
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
            <PublicArticleSection title="Project handling">
                <p>
                    Maintainers may temporarily disable disputed material while
                    reviewing provenance, permissions, attribution, provider
                    terms, and contributor records. Material may be removed,
                    replaced, restored, or converted to an external reference
                    based on available evidence. Public search and media
                    attachments must stop exposing a credible disputed asset
                    during review.
                </p>
            </PublicArticleSection>
            <PublicArticleSection title="Responses, repeat infringement, and limits">
                <p>
                    Contributors may provide a private response or
                    counter-notice where legally appropriate and operationally
                    supported. The project promises no fixed response time and
                    does not adjudicate ownership beyond what is necessary to
                    manage its content. Repeated knowing infringement may result
                    in contribution or account restrictions.
                </p>
                <p>
                    Public legal intake, jurisdiction-specific fields and
                    deadlines, legal holds, production retention, and a formal
                    counter-notice process remain owner and legal-review
                    decisions.
                </p>
            </PublicArticleSection>
        </>
    );
}
