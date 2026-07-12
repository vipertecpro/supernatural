import { Play, Radio } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import type { ExperienceMedia } from '@/types';

export function MediaInterlude({ media }: { media: ExperienceMedia }) {
    const [playerActive, setPlayerActive] = useState(false);

    return (
        <section
            className="media-interlude"
            aria-labelledby="transmission-title"
        >
            <div className="media-interlude-copy">
                <p className="text-metadata">07 / OPEN THE TRANSMISSION</p>
                <h2 id="transmission-title" className="text-display-md mt-5">
                    A signal waits behind the static.
                </h2>
                <p className="text-body-lg mt-5 text-foreground-secondary">
                    Approved provider media appears only when its source,
                    embedding permission, and attribution are reviewable.
                </p>
            </div>
            {media.transmission ? (
                <div className="transmission-frame">
                    {playerActive ? (
                        <iframe
                            src={media.transmission.embedUrl}
                            title={media.transmission.title}
                            allow="accelerometer; encrypted-media; gyroscope; picture-in-picture"
                            allowFullScreen
                        />
                    ) : (
                        <button
                            type="button"
                            className="transmission-placeholder"
                            onClick={() => setPlayerActive(true)}
                        >
                            <Play aria-hidden="true" />
                            <span>Load official YouTube transmission</span>
                            <small>
                                Activating connects to YouTube's
                                privacy-enhanced player.
                            </small>
                        </button>
                    )}
                    <p className="text-xs text-foreground-muted">
                        {media.transmission.attribution}
                    </p>
                </div>
            ) : (
                <div
                    className="transmission-fallback"
                    role="img"
                    aria-label="No approved official transmission is configured"
                >
                    <Radio aria-hidden="true" />
                    <span className="transmission-wave" />
                    <p>No approved transmission configured</p>
                </div>
            )}
            {media.tmdb.enabled && (
                <div
                    className="media-reel"
                    aria-label="Approved series imagery"
                >
                    {media.tmdb.images.map((image) => (
                        <img
                            key={image.key}
                            src={image.src}
                            srcSet={image.srcSet}
                            sizes="(max-width: 767px) 82vw, 42vw"
                            width={image.width}
                            height={image.height}
                            alt={image.alt}
                            loading="lazy"
                            decoding="async"
                        />
                    ))}
                    <div className="media-attribution">
                        <p>{media.tmdb.attribution}</p>
                        <p>{media.tmdb.notice}</p>
                    </div>
                </div>
            )}
            {media.transmission && !playerActive && (
                <Button variant="ghost" asChild>
                    <a
                        href={media.transmission.canonicalUrl}
                        target="_blank"
                        rel="noreferrer"
                    >
                        View on YouTube
                    </a>
                </Button>
            )}
        </section>
    );
}
