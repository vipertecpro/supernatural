import { useState } from 'react';
import { publicCast } from '@/features/experience/public-cast';

export function CastShowcase() {
    const [activeIndex, setActiveIndex] = useState(0);
    const activeMember = publicCast[activeIndex];

    return (
        <section
            id="cast"
            className="cast-showcase"
            data-immersive-section
            aria-labelledby="cast-showcase-title"
        >
            <header className="cast-showcase-header">
                <p>THE PEOPLE BEHIND THE HUNT</p>
                <h2 id="cast-showcase-title">Cast</h2>
                <span>{String(publicCast.length).padStart(2, '0')}</span>
            </header>

            <div className="cast-showcase-body">
                <figure className="cast-showcase-portrait" aria-live="polite">
                    <img
                        key={activeMember.actor}
                        src={activeMember.image}
                        alt={`${activeMember.actor}, who plays ${activeMember.character}`}
                        width={activeMember.width}
                        height={activeMember.height}
                        loading="lazy"
                        decoding="async"
                        style={{ objectPosition: activeMember.focalPosition }}
                    />
                    <figcaption>
                        <span>{String(activeIndex + 1).padStart(2, '0')}</span>
                        <div
                            key={activeMember.actor}
                            className="cast-showcase-active-copy"
                        >
                            <span className="cast-showcase-code">
                                ({activeMember.code})
                            </span>
                            <strong>{activeMember.actor}</strong>
                            <small>{activeMember.character}</small>
                            <p className="cast-showcase-summary">
                                {activeMember.summary}
                            </p>
                        </div>
                    </figcaption>
                </figure>

                <div className="cast-showcase-index">
                    <div className="cast-showcase-columns" aria-hidden="true">
                        <span>No.</span>
                        <span>Actor</span>
                        <span>Character</span>
                    </div>
                    {publicCast.map((member, index) => (
                        <button
                            key={member.actor}
                            type="button"
                            data-active={activeIndex === index}
                            aria-pressed={activeIndex === index}
                            onClick={() => setActiveIndex(index)}
                            onFocus={() => setActiveIndex(index)}
                            onMouseEnter={() => setActiveIndex(index)}
                        >
                            <span>{String(index + 1).padStart(2, '0')}</span>
                            <strong>{member.actor}</strong>
                            <span>
                                {member.character}
                                <small>{member.role}</small>
                            </span>
                        </button>
                    ))}
                </div>
            </div>

            <details className="cast-showcase-credits">
                <summary>Portrait credits</summary>
                <ul>
                    {publicCast.map((member) => (
                        <li key={member.actor}>
                            <a
                                href={member.sourceUrl}
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                {member.actor}
                            </a>{' '}
                            — {member.artist}, {member.license}
                        </li>
                    ))}
                </ul>
            </details>
        </section>
    );
}
