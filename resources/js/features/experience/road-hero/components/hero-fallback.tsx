export function HeroFallback({
    reason,
}: {
    reason: 'reduced' | 'webgl' | 'loading';
}) {
    return (
        <div
            className="road-hero-fallback"
            data-fallback-reason={reason}
            aria-hidden="true"
            user-data-asset="road-hero-fallback"
        >
            <div className="road-hero-fallback-sky" />
            <div className="road-hero-fallback-moon" />
            <div className="road-hero-fallback-forest road-hero-fallback-forest-far" />
            <div className="road-hero-fallback-forest road-hero-fallback-forest-near" />
            <div className="road-hero-fallback-road">
                <span />
                <span />
                <span />
            </div>
            <div className="road-hero-fallback-car">
                <span className="road-hero-fallback-car-body" />
                <span className="road-hero-fallback-tail road-hero-fallback-tail-left" />
                <span className="road-hero-fallback-tail road-hero-fallback-tail-right" />
                <span className="road-hero-fallback-wheel road-hero-fallback-wheel-left" />
                <span className="road-hero-fallback-wheel road-hero-fallback-wheel-right" />
            </div>
            <div className="road-hero-fallback-entrance" />
            <div className="road-hero-fallback-fog" />
        </div>
    );
}
