import { Link, usePage } from '@inertiajs/react';
import { ArrowDown, ArrowRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { about, dashboard, register } from '@/routes';
import { useExperience } from '../../experience-context';
import { HeroControls } from './hero-controls';

export function HeroOverlay() {
    const { auth } = usePage().props;
    const { mode, quality } = useExperience();

    return (
        <div className="road-hero-overlay">
            <a href="#archive-opens" className="sr-only focus:not-sr-only">
                Skip the road introduction
            </a>
            <div className="road-hero-frame" aria-hidden="true">
                <span>67° 03' 18.4" N</span>
                <span>ARCHIVE SIGNAL / 001</span>
            </div>
            <div className="road-hero-copy road-hero-copy-primary">
                <p className="road-hero-kicker" data-road-hero-reveal>
                    Every story leaves a signal
                </p>
                <h1 id="road-hero-title" tabIndex={-1} data-road-hero-reveal>
                    <span>The</span>
                    <span>Archive</span>
                </h1>
                <p className="road-hero-statement" data-road-hero-reveal>
                    A fan-made companion to the Supernatural television
                    series—follow the road through hunts, creatures,
                    evidence-led lore, and the stories that refuse to stay
                    buried.
                </p>
                <div className="road-hero-actions" data-road-hero-reveal>
                    <Button size="lg" asChild>
                        <Link
                            href={auth.user ? dashboard() : register()}
                            viewTransition
                        >
                            {auth.user
                                ? 'Enter the archive'
                                : 'Begin the journey'}
                            <ArrowRight />
                        </Link>
                    </Button>
                    <Button size="lg" variant="outline" asChild>
                        <Link href={about()} viewTransition>
                            About the project
                        </Link>
                    </Button>
                </div>
            </div>
            <HeroControls />
            <div className="road-hero-scroll" data-road-hero-reveal>
                <span>Scroll to drive</span>
                <ArrowDown />
            </div>
            <div className="road-hero-tier" aria-live="polite">
                {mode} / {quality}
            </div>
        </div>
    );
}
