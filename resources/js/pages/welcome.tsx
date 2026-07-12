import { Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import { CastShowcase } from '@/components/public/cast-showcase';
import { PublicHead } from '@/components/public/public-head';
import {
    PublicArticleSection,
    PublicPageIntro,
} from '@/components/public/public-page-intro';
import { Button } from '@/components/ui/button';
import { supernaturalMinimalImages } from '@/features/experience/public-collage-images';
import { about, register } from '@/routes';
import type { PublicPageProps } from '@/types';

export default function Welcome({ publicSite }: PublicPageProps) {
    const featureImages = [
        supernaturalMinimalImages[0],
        supernaturalMinimalImages[9],
        supernaturalMinimalImages[22],
        supernaturalMinimalImages[48],
    ];
    const ribbonImages = [
        supernaturalMinimalImages[1],
        supernaturalMinimalImages[12],
        supernaturalMinimalImages[20],
        supernaturalMinimalImages[31],
        supernaturalMinimalImages[45],
        supernaturalMinimalImages[58],
    ];

    return (
        <>
            <PublicHead publicSite={publicSite} />
            <div
                id="archive-opens"
                className="collage-home-content public-content-shell w-full pb-20 lg:pb-32"
                tabIndex={-1}
            >
                <article className="public-prose text-body max-w-none min-w-0">
                    <PublicPageIntro
                        eyebrow="SUPERNATURAL / THE ARCHIVE"
                        title="The Archive remembers what the road leaves behind."
                        description="A fan-made archive for the television series Supernatural—its hunters, creatures, symbols, cases, sacrifices, and the long road connecting them all."
                        variant="archive"
                    >
                        <div className="mt-8 flex flex-wrap gap-3">
                            {publicSite.registrationAvailable && (
                                <Button asChild>
                                    <Link href={register()}>
                                        Enter the archive <ArrowRight />
                                    </Link>
                                </Button>
                            )}
                            <Button variant="outline" asChild>
                                <Link href={about()}>About this project</Link>
                            </Button>
                            <Button variant="outline" asChild>
                                <a href="#cast">Meet the cast</a>
                            </Button>
                        </div>
                    </PublicPageIntro>

                    <section
                        className="archive-story-ribbon"
                        aria-label="Supernatural visual archive"
                        data-immersive-section
                    >
                        <div
                            className="archive-story-marquee"
                            aria-hidden="true"
                        >
                            <span>THE ROAD · THE CASES · THE LORE · </span>
                            <span>THE ROAD · THE CASES · THE LORE · </span>
                        </div>
                        <div className="archive-story-track">
                            {[...ribbonImages, ...ribbonImages].map(
                                (image, index) => (
                                    <figure
                                        key={`${image.id}-${index}`}
                                        aria-hidden={
                                            index >= ribbonImages.length
                                        }
                                    >
                                        <img
                                            src={image.src}
                                            alt={
                                                index < ribbonImages.length
                                                    ? (image.alt ?? '')
                                                    : ''
                                            }
                                            width={image.width}
                                            height={image.height}
                                            loading={
                                                index < 3 ? 'eager' : 'lazy'
                                            }
                                            decoding="async"
                                            style={{
                                                objectPosition:
                                                    image.focalPosition,
                                            }}
                                        />
                                    </figure>
                                ),
                            )}
                        </div>
                    </section>

                    <CastShowcase />

                    <PublicArticleSection
                        title="Spirits leave patterns behind."
                        image={featureImages[0]}
                    >
                        <p>
                            Follow the unfinished business, haunted places,
                            objects, witnesses, and lore that turn an ordinary
                            case into something the brothers cannot leave
                            behind.
                        </p>
                    </PublicArticleSection>

                    <PublicArticleSection
                        title="Demons always want a deal."
                        image={featureImages[1]}
                    >
                        <p>
                            Trace contracts, possessions, traps, omens, and the
                            impossible choices that make every victory cost more
                            than it promised.
                        </p>
                    </PublicArticleSection>

                    <PublicArticleSection
                        title="Every monster has rules."
                        image={featureImages[2]}
                    >
                        <p>
                            Compare creatures, weaknesses, disguises, hunting
                            methods, and the moments when the line between
                            monster and human becomes difficult to see.
                        </p>
                    </PublicArticleSection>

                    <PublicArticleSection
                        title="Family is the final protection."
                        image={featureImages[3]}
                    >
                        <p>
                            Revisit the bonds, losses, found family, and
                            stubborn hope that keep the engine running when the
                            world should already have ended.
                        </p>
                    </PublicArticleSection>

                    <section
                        className="archive-closing-call"
                        data-immersive-section
                    >
                        <p>JOIN THE HUNT</p>
                        <h2>Every case leaves something worth remembering.</h2>
                        <div>
                            {publicSite.registrationAvailable ? (
                                <Button asChild>
                                    <Link href={register()}>
                                        Enter the archive <ArrowRight />
                                    </Link>
                                </Button>
                            ) : null}
                            <Button variant="outline" asChild>
                                <Link href={about()}>Read our field notes</Link>
                            </Button>
                        </div>
                    </section>
                </article>
            </div>
        </>
    );
}
