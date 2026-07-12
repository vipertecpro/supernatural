import { useFrame } from '@react-three/fiber';
import { useRef } from 'react';
import * as THREE from 'three';

export function Atmosphere({ isLight }: { isLight: boolean }) {
    const theme = useRef(isLight ? 1 : 0);
    const darkSky = new THREE.Color('#02060b');
    const lightSky = new THREE.Color('#9ba7a3');
    const darkFog = new THREE.Color('#071018');
    const lightFog = new THREE.Color('#aeb9b3');

    useFrame(({ scene }, delta) => {
        theme.current = THREE.MathUtils.damp(
            theme.current,
            isLight ? 1 : 0,
            2.2,
            delta,
        );
        const sky = darkSky.clone().lerp(lightSky, theme.current);
        const fog = darkFog.clone().lerp(lightFog, theme.current);
        scene.background = sky;
        scene.fog = new THREE.FogExp2(fog, isLight ? 0.029 : 0.042);
    });

    return (
        <>
            <ambientLight
                intensity={isLight ? 1.25 : 0.22}
                color={isLight ? '#dce4dd' : '#57758a'}
            />
            <directionalLight
                castShadow
                position={isLight ? [-8, 14, 8] : [6, 12, -8]}
                intensity={isLight ? 2.6 : 1.15}
                color={isLight ? '#e8eadc' : '#9abbd1'}
                shadow-mapSize={[1024, 1024]}
            />
            {!isLight && (
                <pointLight
                    position={[-12, 9, -34]}
                    intensity={30}
                    distance={42}
                    color="#7b9fd0"
                />
            )}
        </>
    );
}
