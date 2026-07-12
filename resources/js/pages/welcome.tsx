import { PublicHead } from '@/components/public/public-head';
import { RoadHero } from '@/features/experience/road-hero/components/road-hero';
import type { ExperienceMedia, PublicPageProps } from '@/types';

export default function Welcome({
    publicSite,
}: PublicPageProps & { experienceMedia: ExperienceMedia }) {
    return (
        <>
            <PublicHead publicSite={publicSite} />
            <RoadHero />
            <div
                id="archive-opens"
                className="road-hero-exit"
                tabIndex={-1}
                aria-label="Road introduction complete"
            >
                <span aria-hidden="true" />
            </div>
        </>
    );
}
