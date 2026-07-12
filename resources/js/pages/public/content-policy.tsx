import { PublicHead } from '@/components/public/public-head';
import {
    PublicArticleSection,
    PublicPageIntro,
} from '@/components/public/public-page-intro';
import type { PublicPageProps } from '@/types';

export default function ContentPolicy({ publicSite }: PublicPageProps) {
    return (
        <>
            <PublicHead publicSite={publicSite} />
            <PublicPageIntro
                eyebrow="TRUST / CONTENT POLICY"
                title="Create with context. Contribute with permission."
                description="This public summary reflects the repository Content Policy and remains subject to legal and operational review. It does not replace the canonical policy source or legal advice."
                variant="boundary"
            />
            <PublicArticleSection title="Rights, sources, and attribution">
                <ul>
                    <li>
                        Record the source, creator or publisher when known,
                        licence, attribution, intended use, and relevant
                        limitations.
                    </li>
                    <li>
                        Do not present unknown commercial, derivative, hosting,
                        or embedding rights as permission.
                    </li>
                    <li>
                        Contributors must own submitted material or have
                        permission for the intended submission and use.
                    </li>
                    <li>
                        Fan art requires creator permission and attribution
                        unless a clear applicable licence permits use.
                    </li>
                    <li>
                        Do not rehost episodes, music, protected video, fonts,
                        logos, promotional media, full transcripts, or
                        substantial protected text.
                    </li>
                    <li>
                        Provider-authorized embedding is distinct from
                        downloading, copying, or hosting the same media.
                    </li>
                    <li>
                        Scraping or bulk ingestion requires explicit technical,
                        legal, and provider-policy review.
                    </li>
                </ul>
            </PublicArticleSection>
            <PublicArticleSection title="User and community content">
                <p>
                    Illegal material, credible threats, harassment, hate or
                    abusive conduct, sexual exploitation, non-consensual
                    intimate content, doxxing, impersonation, malware, fraud,
                    spam, and repeated infringement are prohibited. Sexual
                    content involving minors is prohibited without exception and
                    must be escalated according to applicable law.
                </p>
            </PublicArticleSection>
            <PublicArticleSection title="Spoilers">
                <p>
                    Material reveals must use the spoiler-classification system.
                    Warnings and structured progress boundaries protect pages,
                    search, notifications, community content, and future
                    clients. Deliberately evading these controls may be
                    moderated.
                </p>
            </PublicArticleSection>
            <PublicArticleSection title="Moderation, privacy, and appeals">
                <p>
                    Moderators may label, hide, restrict, or remove content;
                    restrict accounts; preserve minimum audit evidence; and
                    escalate safety or rights concerns. Reports remain private
                    to the reporter and authorized case-scoped reviewers. A
                    report does not prove a violation or apply enforcement
                    automatically. Subjects do not receive reporter identity,
                    private evidence, or internal notes.
                </p>
                <p>
                    Users should receive a proportionate appeal path where
                    supported. Repeated harassment, spam, impersonation, or
                    infringement may result in permanent restrictions. Personal
                    blocks and mutes remain private safety preferences, not
                    public accusations.
                </p>
            </PublicArticleSection>
        </>
    );
}
