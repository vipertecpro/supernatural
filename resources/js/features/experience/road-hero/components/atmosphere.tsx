import { useFrame } from '@react-three/fiber';
import { useRef } from 'react';
import type { MutableRefObject } from 'react';
import * as THREE from 'three';
import type { RoadHeroRuntime } from '../types';

export function Atmosphere({
    isLight,
    runtime,
}: {
    isLight: boolean;
    runtime: MutableRefObject<RoadHeroRuntime>;
}) {
    const theme = useRef(isLight ? 1 : 0);
    const darkSky = new THREE.Color('#020202');
    const lightSky = new THREE.Color('#a8a8a8');
    const darkFog = new THREE.Color('#0b0b0b');
    const lightFog = new THREE.Color('#b8b8b8');
    const omenSky = new THREE.Color('#080808');
    const omenFog = new THREE.Color('#171717');

    useFrame(({ scene }, delta) => {
        theme.current = THREE.MathUtils.damp(
            theme.current,
            isLight ? 1 : 0,
            2.2,
            delta,
        );
        const omen = isLight
            ? 0
            : 1 -
              THREE.MathUtils.smoothstep(runtime.current.progress, 0.08, 0.34);
        const sky = darkSky
            .clone()
            .lerp(omenSky, omen * 0.76)
            .lerp(lightSky, theme.current);
        const fog = darkFog
            .clone()
            .lerp(omenFog, omen * 0.68)
            .lerp(lightFog, theme.current);
        scene.background = sky;
        scene.fog = new THREE.FogExp2(fog, isLight ? 0.029 : 0.042);
    });

    return (
        <>
            <ambientLight
                intensity={isLight ? 1.25 : 0.22}
                color={isLight ? '#dedede' : '#707070'}
            />
            <hemisphereLight
                args={[
                    isLight ? '#e2e2e2' : '#353535',
                    isLight ? '#373737' : '#080808',
                    isLight ? 0.75 : 0.3,
                ]}
            />
            <directionalLight
                castShadow
                position={isLight ? [-8, 14, 8] : [6, 12, -8]}
                intensity={isLight ? 2.6 : 1.15}
                color={isLight ? '#e8e8e8' : '#b0b0b0'}
                shadow-mapSize={[1024, 1024]}
            />
            {!isLight && (
                <pointLight
                    position={[-12, 9, -34]}
                    intensity={30}
                    distance={42}
                    color="#a5a5a5"
                />
            )}
        </>
    );
}
