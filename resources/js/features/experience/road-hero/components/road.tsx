/* eslint-disable react-hooks/immutability -- R3F frame state is intentionally mutable outside React rendering. */
import { useFrame } from '@react-three/fiber';
import { useMemo, useRef } from 'react';
import type { MutableRefObject } from 'react';
import * as THREE from 'three';
import type { RoadHeroRuntime } from '../types';

const segmentLength = 18;
const segmentCount = 9;

export function Road({
    runtime,
    isLight,
}: {
    runtime: MutableRefObject<RoadHeroRuntime>;
    isLight: boolean;
}) {
    const group = useRef<THREE.Group>(null);
    const markings = useMemo(
        () => Array.from({ length: segmentCount * 3 }, (_, index) => index),
        [],
    );

    useFrame((_, delta) => {
        const road = group.current;

        if (!road || !runtime.current.active) {
            return;
        }

        const target = (runtime.current.progress * 126) % segmentLength;
        road.position.z = THREE.MathUtils.damp(
            road.position.z,
            target,
            5,
            delta,
        );
        runtime.current.velocity = THREE.MathUtils.damp(
            runtime.current.velocity,
            0,
            4,
            delta,
        );
    });

    return (
        <group ref={group}>
            {Array.from({ length: segmentCount }, (_, index) => {
                const z = 8 - index * segmentLength;

                return (
                    <group key={z} position={[0, -0.66, z]}>
                        <mesh
                            rotation-x={-Math.PI / 2}
                            receiveShadow
                            userData={{ asset: 'wet-forest-road' }}
                        >
                            <planeGeometry
                                args={[11.5, segmentLength + 0.08]}
                            />
                            <meshStandardMaterial
                                color={isLight ? '#303738' : '#070a0c'}
                                roughness={isLight ? 0.34 : 0.23}
                                metalness={isLight ? 0.22 : 0.42}
                            />
                        </mesh>
                        {[-5.45, 5.45].map((x) => (
                            <mesh
                                key={x}
                                position={[x, 0.012, 0]}
                                rotation-x={-Math.PI / 2}
                            >
                                <planeGeometry args={[0.11, segmentLength]} />
                                <meshBasicMaterial
                                    color={isLight ? '#d8d4bd' : '#9da9a5'}
                                    transparent
                                    opacity={0.54}
                                />
                            </mesh>
                        ))}
                        <mesh
                            position={[0, -0.025, 0]}
                            rotation-x={-Math.PI / 2}
                        >
                            <planeGeometry args={[18, segmentLength]} />
                            <meshStandardMaterial
                                color={isLight ? '#30352f' : '#030504'}
                                roughness={0.96}
                            />
                        </mesh>
                    </group>
                );
            })}
            {markings.map((index) => {
                const z = 8 - index * 5.8;

                return (
                    <mesh
                        key={z}
                        position={[0, -0.635, z]}
                        rotation-x={-Math.PI / 2}
                    >
                        <planeGeometry args={[0.12, 2.7]} />
                        <meshBasicMaterial
                            color={isLight ? '#e7e1c5' : '#d8d6c6'}
                            transparent
                            opacity={0.72}
                        />
                    </mesh>
                );
            })}
        </group>
    );
}
