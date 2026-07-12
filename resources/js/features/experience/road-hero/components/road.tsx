/* eslint-disable react-hooks/immutability -- R3F frame state is intentionally mutable outside React rendering. */
import { useTexture } from '@react-three/drei';
import { useFrame } from '@react-three/fiber';
import { useEffect, useMemo, useRef } from 'react';
import type { MutableRefObject } from 'react';
import * as THREE from 'three';
import { getRoadHeroPose } from '../motion';
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
    const [roadColor, roadNormal, roadRoughness] = useTexture([
        '/media/road-journey/polyhaven/asphalt-01-diffuse-1k.jpg',
        '/media/road-journey/polyhaven/asphalt-01-normal-1k.jpg',
        '/media/road-journey/polyhaven/asphalt-01-roughness-1k.jpg',
    ]);
    const markings = useMemo(
        () => Array.from({ length: segmentCount * 3 }, (_, index) => index),
        [],
    );

    useEffect(() => {
        roadColor.colorSpace = THREE.SRGBColorSpace;
        [roadColor, roadNormal, roadRoughness].forEach((texture) => {
            texture.wrapS = THREE.RepeatWrapping;
            texture.wrapT = THREE.RepeatWrapping;
            texture.repeat.set(1.5, 3.2);
            texture.needsUpdate = true;
        });
    }, [roadColor, roadNormal, roadRoughness]);

    useFrame((_, delta) => {
        const road = group.current;

        if (!road || !runtime.current.active) {
            return;
        }

        const progress = runtime.current.progress;
        const pose = getRoadHeroPose(progress);
        const targetSpeed = 8.5 + progress * 4.5;
        runtime.current.driveSpeed = THREE.MathUtils.damp(
            runtime.current.driveSpeed,
            targetSpeed,
            2.8,
            delta,
        );
        runtime.current.distance +=
            runtime.current.driveSpeed * delta * pose.travelDirection;
        const target =
            ((runtime.current.distance % segmentLength) + segmentLength) %
            segmentLength;
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
                                color={isLight ? '#363636' : '#090909'}
                                map={roadColor}
                                normalMap={roadNormal}
                                normalScale={new THREE.Vector2(0.42, 0.42)}
                                roughnessMap={roadRoughness}
                                roughness={isLight ? 0.48 : 0.31}
                                metalness={isLight ? 0.18 : 0.34}
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
                                    color={isLight ? '#d4d4d4' : '#a3a3a3'}
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
                                color={isLight ? '#343434' : '#040404'}
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
                            color={isLight ? '#e3e3e3' : '#d5d5d5'}
                            transparent
                            opacity={0.72}
                        />
                    </mesh>
                );
            })}
        </group>
    );
}
