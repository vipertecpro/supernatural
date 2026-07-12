import { useFrame } from '@react-three/fiber';
import { useMemo, useRef } from 'react';
import type { MutableRefObject } from 'react';
import type * as THREE from 'three';
import type { ExperienceQuality } from '../../types';
import type { RoadHeroRuntime } from '../types';

export function Weather({
    runtime,
    quality,
    isLight,
}: {
    runtime: MutableRefObject<RoadHeroRuntime>;
    quality: Exclude<ExperienceQuality, 'fallback'>;
    isLight: boolean;
}) {
    const count = quality === 'high' ? 900 : quality === 'medium' ? 520 : 220;
    const points = useRef<THREE.Points>(null);
    const positions = useMemo(() => {
        const values = new Float32Array(count * 3);

        for (let index = 0; index < count; index += 1) {
            const seed = Math.sin(index * 91.77) * 43758.5453;
            const random = seed - Math.floor(seed);
            values[index * 3] = (random - 0.5) * 25;
            values[index * 3 + 1] = ((index * 17) % 100) * 0.085 - 0.4;
            values[index * 3 + 2] = -((index * 29) % 120) * 0.38 + 8;
        }

        return values;
    }, [count]);

    useFrame((_, delta) => {
        if (!points.current || !runtime.current.active) {
            return;
        }

        points.current.position.y -=
            delta * (2.2 + runtime.current.progress * 3);
        points.current.position.z += delta * (1.4 + runtime.current.velocity);

        if (points.current.position.y < -3) {
            points.current.position.y = 3;
        }

        if (points.current.position.z > 12) {
            points.current.position.z = 0;
        }
    });

    return (
        <group userData={{ asset: 'road-weather' }}>
            <points ref={points}>
                <bufferGeometry>
                    <bufferAttribute
                        attach="attributes-position"
                        args={[positions, 3]}
                    />
                </bufferGeometry>
                <pointsMaterial
                    color={isLight ? '#e3e7e3' : '#b9cad0'}
                    size={quality === 'high' ? 0.035 : 0.028}
                    transparent
                    opacity={isLight ? 0.3 : 0.5}
                    depthWrite={false}
                />
            </points>
            {[-32, -50, -68].map((z, index) => (
                <mesh
                    key={z}
                    position={[(index - 1) * 7, 8 + index, z]}
                    rotation-x={Math.PI / 2}
                >
                    <planeGeometry args={[18, 8]} />
                    <meshBasicMaterial
                        color={isLight ? '#c6ceca' : '#111c24'}
                        transparent
                        opacity={0.13}
                        depthWrite={false}
                    />
                </mesh>
            ))}
        </group>
    );
}
