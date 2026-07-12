import { RoundedBox } from '@react-three/drei';
import { useFrame } from '@react-three/fiber';
import { useRef } from 'react';
import type { MutableRefObject } from 'react';
import * as THREE from 'three';
import { getRoadHeroPose } from '../motion';
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
        const speed = runtime.current.driveSpeed + runtime.current.velocity;
        const pose = getRoadHeroPose(runtime.current.progress);

        if (wheels.current) {
            wheels.current.children.forEach((wheel) => {
                wheel.rotation.x -= delta * speed * 2.4;
            });
        }

        if (vehicle.current) {
            vehicle.current.position.x = THREE.MathUtils.damp(
                vehicle.current.position.x,
                pose.x,
                6.2,
                delta,
            );
            vehicle.current.position.z = THREE.MathUtils.damp(
                vehicle.current.position.z,
                pose.z,
                5.8,
                delta,
            );
            vehicle.current.position.y =
                -0.03 +
                Math.sin(clock.elapsedTime * (3.4 + speed * 0.08)) * 0.018;
            vehicle.current.rotation.z =
                Math.sin(clock.elapsedTime * 0.72) * 0.004 -
                pose.zig * 0.018 -
                pose.turnArc * 0.055;
            vehicle.current.rotation.y = THREE.MathUtils.damp(
                vehicle.current.rotation.y,
                pose.yaw,
                6.5,
                delta,
            );
        }
    });

    const body = isLight ? '#101010' : '#030303';

    return (
        <group
            ref={vehicle}
            position={[0, -0.18, 0]}
            scale={0.92}
            userData={{ asset: 'archive-roadster' }}
        >
            <RoundedBox
                args={[2.5, 0.48, 4.9]}
                radius={0.22}
                smoothness={4}
                position={[0, 0.14, -0.08]}
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
                args={[1.92, 0.64, 1.76]}
                radius={0.22}
                smoothness={4}
                position={[0, 0.72, 0.34]}
                castShadow
            >
                <meshPhysicalMaterial
                    color={body}
                    roughness={0.18}
                    metalness={0.78}
                    clearcoat={1}
                />
            </RoundedBox>
            <RoundedBox
                args={[2.22, 0.22, 1.45]}
                radius={0.12}
                smoothness={3}
                position={[0, 0.48, 1.48]}
                rotation-x={-0.05}
                castShadow
            >
                <meshPhysicalMaterial
                    color={body}
                    roughness={0.17}
                    metalness={0.86}
                    clearcoat={1}
                />
            </RoundedBox>
            {[-1.04, 1.04].map((x) => (
                <RoundedBox
                    key={`rear-quarter-${x}`}
                    args={[0.42, 0.5, 1.78]}
                    radius={0.2}
                    smoothness={3}
                    position={[x, 0.13, 1.18]}
                    castShadow
                >
                    <meshPhysicalMaterial
                        color={body}
                        roughness={0.19}
                        metalness={0.82}
                        clearcoat={1}
                    />
                </RoundedBox>
            ))}
            <mesh position={[0, 0.84, -0.48]} rotation-x={-0.23}>
                <boxGeometry args={[1.78, 0.48, 0.045]} />
                <meshPhysicalMaterial
                    color="#101010"
                    roughness={0.08}
                    metalness={0.35}
                    transmission={0.08}
                />
            </mesh>
            <mesh position={[0, 0.84, 1.34]} rotation-x={0.2}>
                <boxGeometry args={[1.76, 0.44, 0.045]} />
                <meshPhysicalMaterial color="#111111" roughness={0.12} />
            </mesh>
            <mesh position={[0, 0.2, -2.44]}>
                <boxGeometry args={[2.18, 0.34, 0.11]} />
                <meshStandardMaterial
                    color="#151515"
                    metalness={0.9}
                    roughness={0.22}
                />
            </mesh>
            <mesh position={[0, 0.06, 2.37]}>
                <boxGeometry args={[2.2, 0.18, 0.12]} />
                <meshStandardMaterial
                    color="#b7b7b7"
                    metalness={1}
                    roughness={0.2}
                />
            </mesh>
            <mesh position={[0, 0.4, 2.43]} rotation-x={-0.08}>
                <boxGeometry args={[2.08, 0.56, 0.08]} />
                <meshStandardMaterial
                    color="#0a0a0a"
                    metalness={0.72}
                    roughness={0.28}
                />
            </mesh>
            {[-0.72, 0.72].map((x) => (
                <mesh key={x} position={[x, 0.41, 2.48]}>
                    <boxGeometry args={[0.62, 0.15, 0.05]} />
                    <meshStandardMaterial
                        color="#c8c8c8"
                        emissive="#f5f5f5"
                        emissiveIntensity={4}
                    />
                </mesh>
            ))}
            {[-0.38, 0.38].map((x) => (
                <mesh
                    key={`exhaust-${x}`}
                    position={[x, -0.11, 2.54]}
                    rotation-x={Math.PI / 2}
                >
                    <cylinderGeometry args={[0.055, 0.07, 0.35, 12]} />
                    <meshStandardMaterial
                        color="#a0a0a0"
                        metalness={1}
                        roughness={0.24}
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
