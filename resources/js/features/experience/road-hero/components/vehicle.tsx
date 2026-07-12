import { RoundedBox } from '@react-three/drei';
import { useFrame } from '@react-three/fiber';
import { useRef } from 'react';
import type { MutableRefObject } from 'react';
import * as THREE from 'three';
import type { RoadHeroRuntime } from '../types';
import { Headlights } from './headlights';

export function Vehicle({
    runtime,
    isLight,
}: {
    runtime: MutableRefObject<RoadHeroRuntime>;
    isLight: boolean;
}) {
    const vehicle = useRef<THREE.Group>(null);
    const wheels = useRef<THREE.Group>(null);

    useFrame(({ clock }, delta) => {
        const speed =
            2.4 + runtime.current.progress * 8 + runtime.current.velocity;

        if (wheels.current) {
            wheels.current.children.forEach((wheel) => {
                wheel.rotation.x -= delta * speed * 2.4;
            });
        }

        if (vehicle.current) {
            vehicle.current.position.y =
                -0.03 +
                Math.sin(clock.elapsedTime * (3.4 + speed * 0.08)) * 0.018;
            vehicle.current.rotation.z =
                Math.sin(clock.elapsedTime * 0.72) * 0.004;
            vehicle.current.rotation.y = THREE.MathUtils.damp(
                vehicle.current.rotation.y,
                Math.sin(runtime.current.progress * Math.PI * 1.4) * 0.025,
                3,
                delta,
            );
        }
    });

    const body = isLight ? '#0c1011' : '#020304';

    return (
        <group
            ref={vehicle}
            position={[0, -0.18, 0]}
            scale={0.92}
            userData={{ asset: 'archive-roadster' }}
        >
            <RoundedBox
                args={[2.55, 0.62, 4.75]}
                radius={0.18}
                smoothness={4}
                position={[0, 0.2, -0.08]}
                castShadow
            >
                <meshPhysicalMaterial
                    color={body}
                    roughness={0.2}
                    metalness={0.82}
                    clearcoat={1}
                    clearcoatRoughness={0.16}
                />
            </RoundedBox>
            <RoundedBox
                args={[2.05, 0.72, 1.82]}
                radius={0.16}
                smoothness={4}
                position={[0, 0.77, 0.45]}
                castShadow
            >
                <meshPhysicalMaterial
                    color={body}
                    roughness={0.18}
                    metalness={0.78}
                    clearcoat={1}
                />
            </RoundedBox>
            <mesh position={[0, 0.84, -0.48]} rotation-x={-0.23}>
                <boxGeometry args={[1.78, 0.48, 0.045]} />
                <meshPhysicalMaterial
                    color="#071116"
                    roughness={0.08}
                    metalness={0.35}
                    transmission={0.08}
                />
            </mesh>
            <mesh position={[0, 0.84, 1.34]} rotation-x={0.2}>
                <boxGeometry args={[1.76, 0.44, 0.045]} />
                <meshPhysicalMaterial color="#081217" roughness={0.12} />
            </mesh>
            <mesh position={[0, 0.2, -2.44]}>
                <boxGeometry args={[2.18, 0.34, 0.11]} />
                <meshStandardMaterial
                    color="#111416"
                    metalness={0.9}
                    roughness={0.22}
                />
            </mesh>
            <mesh position={[0, 0.06, 2.37]}>
                <boxGeometry args={[2.2, 0.18, 0.12]} />
                <meshStandardMaterial
                    color="#b9b6a9"
                    metalness={1}
                    roughness={0.2}
                />
            </mesh>
            {[-0.72, 0.72].map((x) => (
                <mesh key={x} position={[x, 0.35, 2.43]}>
                    <boxGeometry args={[0.42, 0.17, 0.05]} />
                    <meshStandardMaterial
                        color="#be1714"
                        emissive="#d21b16"
                        emissiveIntensity={4}
                    />
                </mesh>
            ))}
            <group ref={wheels}>
                {[-1.19, 1.19].flatMap((x) =>
                    [-1.42, 1.38].map((z) => (
                        <mesh
                            key={`${x}-${z}`}
                            position={[x, -0.18, z]}
                            rotation-z={Math.PI / 2}
                            castShadow
                        >
                            <cylinderGeometry args={[0.47, 0.47, 0.3, 24]} />
                            <meshStandardMaterial
                                color="#030303"
                                roughness={0.82}
                                metalness={0.18}
                            />
                        </mesh>
                    )),
                )}
            </group>
            <Headlights isLight={isLight} />
        </group>
    );
}
