import { Canvas } from '@react-three/fiber';
import type { RoadHeroSceneProps } from '../types';
import { ArchiveEntrance } from './archive-entrance';
import { Atmosphere } from './atmosphere';
import { CreatureEncounters } from './creature-encounters';
import { Forest } from './forest';
import { HeroCamera } from './hero-camera';
import { OmenSky } from './omen-sky';
import { Road } from './road';
import { Vehicle } from './vehicle';
import { Weather } from './weather';

export default function RoadScene({
    appearance,
    quality,
    runtime,
    onContextLost,
    onReady,
}: RoadHeroSceneProps) {
    const isLight = appearance === 'light';

    return (
        <Canvas
            aria-hidden="true"
            shadows={quality === 'high'}
            camera={{ position: [0, 2.7, 7.8], fov: 44, near: 0.1, far: 180 }}
            dpr={
                quality === 'high'
                    ? [1, 1.65]
                    : quality === 'medium'
                      ? [1, 1.35]
                      : 1
            }
            gl={{
                antialias: quality !== 'low',
                powerPreference: 'high-performance',
                alpha: false,
            }}
            onCreated={({ gl }) => {
                gl.toneMappingExposure = isLight ? 0.92 : 1.08;
                gl.domElement.addEventListener(
                    'webglcontextlost',
                    onContextLost,
                    { once: true },
                );
                onReady();
            }}
        >
            <Atmosphere isLight={isLight} runtime={runtime} />
            <OmenSky runtime={runtime} />
            <Road runtime={runtime} isLight={isLight} />
            <Forest runtime={runtime} quality={quality} isLight={isLight} />
            <ArchiveEntrance runtime={runtime} isLight={isLight} />
            <CreatureEncounters runtime={runtime} />
            <Vehicle runtime={runtime} isLight={isLight} />
            <Weather runtime={runtime} quality={quality} isLight={isLight} />
            <HeroCamera runtime={runtime} />
        </Canvas>
    );
}
