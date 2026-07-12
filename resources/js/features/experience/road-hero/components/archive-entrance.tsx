import { useFrame } from '@react-three/fiber';
import { useRef } from 'react';
import type { MutableRefObject } from 'react';
import * as THREE from 'three';
import { getRoadHeroPose } from '../motion';
import type { RoadHeroRuntime } from '../types';

export function ArchiveEntrance({
    runtime,
    isLight,
}: {
    runtime: MutableRefObject<RoadHeroRuntime>;
    isLight: boolean;
}) {
    const entrance = useRef<THREE.Group>(null);
    const light = useRef<THREE.PointLight>(null);

    useFrame((_, delta) => {
        const progress = runtime.current.progress;
        const pose = getRoadHeroPose(progress);

        if (entrance.current) {
            const direction = pose.travelDirection < 0 ? 1 : -1;
            entrance.current.position.x = THREE.MathUtils.damp(
                entrance.current.position.x,
                pose.x,
                3,
                delta,
            );
            entrance.current.position.z = THREE.MathUtils.damp(
                entrance.current.position.z,
                pose.z + direction * (50 - progress * 35),
                3,
                delta,
            );
            entrance.current.rotation.y = THREE.MathUtils.damp(
                entrance.current.rotation.y,
                direction > 0 ? Math.PI : 0,
                3,
                delta,
            );
        }

        if (light.current) {
            light.current.intensity = THREE.MathUtils.damp(
                light.current.intensity,
                progress > 0.55 ? 65 + progress * 70 : 5,
                2.5,
                delta,
            );
        }
    });

    return (
        <group
            ref={entrance}
            position={[0, 0, -50]}
            userData={{ asset: 'archive-entrance' }}
        >
            {[-3.1, 3.1].map((x) => (
                <mesh key={x} position={[x, 2.8, 0]} castShadow>
                    <boxGeometry args={[1.2, 7, 1.5]} />
                    <meshStandardMaterial
                        color={isLight ? '#505050' : '#0b0b0b'}
                        roughness={0.82}
                        metalness={0.18}
                    />
                </mesh>
            ))}
            <mesh position={[0, 6.1, 0]}>
                <boxGeometry args={[7.4, 1.2, 1.5]} />
                <meshStandardMaterial
                    color={isLight ? '#505050' : '#0b0b0b'}
                    roughness={0.82}
                    metalness={0.18}
                />
            </mesh>
            <mesh position={[0, 2.65, 0.15]}>
                <planeGeometry args={[5, 5.9]} />
                <meshBasicMaterial
                    color={isLight ? '#f4f4f4' : '#dedede'}
                    transparent
                    opacity={0.9}
                />
            </mesh>
            <pointLight
                ref={light}
                position={[0, 2.7, 3]}
                intensity={5}
                distance={36}
                color={isLight ? '#e8e8e8' : '#d0d0d0'}
            />
        </group>
    );
}
