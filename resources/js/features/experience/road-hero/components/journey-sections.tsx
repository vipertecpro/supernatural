import { Link, usePage } from '@inertiajs/react';
import { ArrowRight, BookOpen, Eye, Flame, Shield } from 'lucide-react';
import type { CSSProperties } from 'react';
import { Button } from '@/components/ui/button';
import { about, dashboard, openSource, register } from '@/routes';

const chapters = [
    {
        id: 'restless-dead',
        index: 'CASE 01 / SPIRITS',
        title: 'Some stories refuse to stay buried.',
        body: 'Trace hauntings, possessions, locations, evidence, and the unfinished history that keeps the dead close to the living.',
        detail: 'Ghosts emerge from memory. The archive keeps the chain of evidence intact.',
        icon: Eye,
        align: 'left',
    },
    {
        id: 'infernal-record',
        index: 'CASE 02 / DEMONS',
        title: 'Know what you are hunting.',
        body: 'Compare lore, aliases, weaknesses, appearances, and citations without flattening decades of contradictory testimony.',
        detail: 'Every claim can point back to a source, revision, and spoiler boundary.',
        icon: Flame,
        align: 'right',
    },
    {
        id: 'bloodlines',
        index: 'CASE 03 / VAMPIRES',
        title: 'Bloodlines leave patterns.',
        body: 'Follow nests, victims, hunters, timelines, and recurring symbols across connected cases and viewing orders.',
        detail: 'Explore safely at your pace with spoiler-aware records and private progress.',
        icon: BookOpen,
        align: 'left',
    },
    {
        id: 'things-in-the-dark',
        index: 'CASE 04 / THE UNKNOWN',
        title: 'Not everything has a name.',
        body: 'Wraiths, shapeshifters, omens, and disputed entities live beside the documented canon—not inside invented certainty.',
        detail: 'Community knowledge stays attributable, reviewable, and protected from harassment.',
        icon: Shield,
        align: 'right',
    },
] as const;

export function JourneySections() {
    const { auth } = usePage().props;

    return (
        <div className="road-journey-story">
            {chapters.map((chapter, index) => {
                const Icon = chapter.icon;

                return (
                    <section
                        key={chapter.id}
                        id={chapter.id}
                        className={`road-journey-chapter road-journey-chapter-${chapter.align}`}
                        style={
                            { top: `${285 + index * 140}svh` } as CSSProperties
                        }
                        aria-labelledby={`${chapter.id}-title`}
                    >
                        <div className="road-journey-casefile">
                            <div className="road-journey-casefile-index">
                                <span>{chapter.index}</span>
                                <Icon aria-hidden="true" />
                            </div>
                            <h2 id={`${chapter.id}-title`}>{chapter.title}</h2>
                            <p>{chapter.body}</p>
                            <p className="road-journey-detail">
                                {chapter.detail}
                            </p>
                            <Link
                                href={about()}
                                className="road-journey-link"
                                viewTransition
                            >
                                Open the case file
                                <ArrowRight aria-hidden="true" />
                            </Link>
                        </div>
                    </section>
                );
            })}

            <section
                className="road-journey-finale"
                aria-labelledby="road-finale-title"
            >
                <div>
                    <p className="road-hero-kicker">
                        END OF THE ROAD / BEGINNING OF THE ARCHIVE
                    </p>
                    <h2 id="road-finale-title">The hunt continues with you.</h2>
                    <p>
                        Build a private journey, follow the evidence, and help
                        shape an open, fandom-built archive without pretending
                        uncertainty is fact.
                    </p>
                    <div className="road-hero-actions">
                        <Button size="lg" asChild>
                            <Link
                                href={auth.user ? dashboard() : register()}
                                viewTransition
                            >
                                {auth.user
                                    ? 'Enter the archive'
                                    : 'Create your archive'}
                                <ArrowRight />
                            </Link>
                        </Button>
                        <Button size="lg" variant="outline" asChild>
                            <Link href={openSource()} viewTransition>
                                See how it is built
                            </Link>
                        </Button>
                    </div>
                </div>
            </section>
        </div>
    );
}
