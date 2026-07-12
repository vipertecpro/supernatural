import { Canvas, useFrame } from '@react-three/fiber';
import { useMemo, useRef, useState } from 'react';
import * as THREE from 'three';
import {
    applyExperienceQualityDrops,
    ScenePerformanceGovernor,
} from './scene-performance-governor';
import type { ExperienceQuality } from './types';

function ArchiveWorld({
    active,
    quality,
}: {
    active: boolean;
    quality: ExperienceQuality;
}) {
    const world = useRef<THREE.Group>(null);
    const headlight = useRef<THREE.Group>(null);
    const seal = useRef<THREE.Group>(null);
    const portal = useRef<THREE.Group>(null);
    const floatingPages = useRef<THREE.Group>(null);
    const particleCount =
        quality === 'high' ? 1800 : quality === 'medium' ? 850 : 320;
    const particles = useMemo(() => {
        const positions = new Float32Array(particleCount * 3);
        const noise = (index: number, salt: number): number => {
            const value = Math.sin(index * 12.9898 + salt) * 43758.5453;

            return value - Math.floor(value);
        };

        for (let index = 0; index < particleCount; index += 1) {
            positions[index * 3] = (noise(index, 1.7) - 0.5) * 34;
            positions[index * 3 + 1] = noise(index, 7.3) * 10 - 2;
            positions[index * 3 + 2] = noise(index, 13.1) * -34 + 8;
        }

        return positions;
    }, [particleCount]);

    useFrame(({ clock, pointer, camera }, delta) => {
        if (!active || document.hidden) {
            return;
        }

        const elapsed = clock.getElapsedTime();

        if (world.current) {
            world.current.rotation.y = THREE.MathUtils.lerp(
                world.current.rotation.y,
                pointer.x * 0.018,
                Math.min(1, delta * 2),
            );
        }

        if (headlight.current) {
            headlight.current.position.z = -26 + ((elapsed * 4.2) % 36);
        }

        if (seal.current) {
            seal.current.rotation.z = elapsed * 0.18;
            seal.current.position.y = Math.sin(elapsed * 0.7) * 0.18 + 2.6;
        }

        if (portal.current) {
            portal.current.rotation.y = Math.sin(elapsed * 0.42) * 0.16;
            portal.current.rotation.z = -elapsed * 0.08;
            portal.current.scale.setScalar(1 + Math.sin(elapsed * 1.4) * 0.035);
        }

        floatingPages.current?.children.forEach((page, index) => {
            page.position.y =
                0.25 +
                (index % 5) * 0.82 +
                Math.sin(elapsed * 0.65 + index) * 0.38;
            page.rotation.y += delta * (0.045 + (index % 3) * 0.018);
            page.rotation.z = Math.sin(elapsed * 0.38 + index) * 0.18;
        });

        if (quality === 'high') {
            camera.position.z = 6.35 + Math.sin(elapsed * 0.18) * 0.32;
            camera.position.y = 1.15 + Math.sin(elapsed * 0.24) * 0.1;
        }

        camera.position.x = THREE.MathUtils.lerp(
            camera.position.x,
            pointer.x * 0.2,
            Math.min(1, delta),
        );
    });

    return (
        <group ref={world}>
            <fog attach="fog" args={['#020506', 5, 31]} />
            <ambientLight intensity={0.32} color="#8eb4b6" />
            <directionalLight
                position={[2, 8, 4]}
                intensity={1.8}
                color="#d8efec"
            />
            <mesh rotation-x={-Math.PI / 2} position={[0, -1.5, -8]}>
                <planeGeometry args={[18, 48]} />
                <meshStandardMaterial
                    color="#05090a"
                    roughness={0.36}
                    metalness={0.34}
                />
            </mesh>
            {[-1.8, 1.8].map((x) => (
                <mesh
                    key={x}
                    rotation-x={-Math.PI / 2}
                    position={[x, -1.47, -8]}
                >
                    <planeGeometry args={[0.035, 44]} />
                    <meshBasicMaterial
                        color="#b9d6cf"
                        transparent
                        opacity={0.54}
                    />
                </mesh>
            ))}
            <group ref={headlight} position={[0, -0.6, -22]}>
                {[-0.46, 0.46].map((x) => (
                    <group key={x} position={[x, 0, 0]}>
                        <mesh>
                            <sphereGeometry args={[0.14, 16, 16]} />
                            <meshBasicMaterial color="#fff1bd" />
                        </mesh>
                        <pointLight
                            intensity={quality === 'low' ? 7 : 14}
                            distance={15}
                            color="#ffe6b0"
                        />
                        <mesh position={[0, 0, 2.7]} rotation-x={Math.PI / 2}>
                            <coneGeometry args={[1.15, 6.5, 24, 1, true]} />
                            <meshBasicMaterial
                                color="#ffe9b5"
                                transparent
                                opacity={0.075}
                                side={THREE.DoubleSide}
                                depthWrite={false}
                                blending={THREE.AdditiveBlending}
                            />
                        </mesh>
                    </group>
                ))}
            </group>
            <mesh position={[0, 1.8, -11.5]}>
                <boxGeometry args={[3.8, 6.6, 1.2]} />
                <meshStandardMaterial
                    color="#0d1112"
                    roughness={0.68}
                    metalness={0.22}
                />
            </mesh>
            <mesh position={[0, 2.1, -10.84]}>
                <boxGeometry args={[2.6, 4.8, 0.08]} />
                <meshStandardMaterial
                    color="#111819"
                    emissive="#73c8c0"
                    emissiveIntensity={0.38}
                />
            </mesh>
            <pointLight
                position={[0, 2.4, -10.1]}
                intensity={quality === 'high' ? 18 : 10}
                distance={13}
                color="#6fd2c8"
            />
            <group ref={portal} position={[0, 2.6, -10.72]}>
                {[0.72, 1.04, 1.42].map((radius, index) => (
                    <mesh key={radius} rotation-z={index * 0.62}>
                        <torusGeometry args={[radius, 0.018, 8, 96]} />
                        <meshBasicMaterial
                            color={index === 1 ? '#c7eee6' : '#6dbeb8'}
                            transparent
                            opacity={0.48 - index * 0.08}
                            blending={THREE.AdditiveBlending}
                        />
                    </mesh>
                ))}
            </group>
            <group ref={seal} position={[0, 2.6, -10.62]}>
                {[0, Math.PI / 3, (Math.PI * 2) / 3].map((rotation) => (
                    <mesh key={rotation} rotation-z={rotation}>
                        <torusGeometry args={[0.75, 0.014, 6, 64]} />
                        <meshBasicMaterial
                            color="#d8f8f1"
                            transparent
                            opacity={0.88}
                            blending={THREE.AdditiveBlending}
                        />
                    </mesh>
                ))}
            </group>
            <group ref={floatingPages}>
                {Array.from({ length: quality === 'low' ? 6 : 15 }).map(
                    (_, index) => (
                        <mesh
                            key={index}
                            position={[
                                ((index % 5) - 2) * 2.15,
                                0.2 + (index % 4) * 0.8,
                                -5.2 - index * 0.48,
                            ]}
                            rotation={[
                                -0.15 + index * 0.01,
                                index * 0.17,
                                index * 0.08,
                            ]}
                        >
                            <planeGeometry args={[1.05, 1.42]} />
                            <meshStandardMaterial
                                color={index % 3 === 0 ? '#bcb9a6' : '#858c84'}
                                side={THREE.DoubleSide}
                                transparent
                                opacity={0.48}
                            />
                        </mesh>
                    ),
                )}
            </group>
            <points>
                <bufferGeometry>
                    <bufferAttribute
                        attach="attributes-position"
                        args={[particles, 3]}
                    />
                </bufferGeometry>
                <pointsMaterial
                    size={quality === 'high' ? 0.038 : 0.03}
                    color="#d9f1e9"
                    transparent
                    opacity={0.68}
                    depthWrite={false}
                />
            </points>
        </group>
    );
}

export default function NightRoadScene({
    active,
    quality,
    onContextLost,
}: {
    active: boolean;
    quality: ExperienceQuality;
    onContextLost: () => void;
}) {
    const [qualityDrops, setQualityDrops] = useState(0);
    const renderQuality = applyExperienceQualityDrops(quality, qualityDrops);

    return (
        <Canvas
            camera={{ position: [0, 1.1, 6.5], fov: 47 }}
            dpr={renderQuality === 'high' ? [1, 1.75] : [1, 1.25]}
            frameloop={active ? 'always' : 'never'}
            gl={{
                antialias: renderQuality !== 'low',
                powerPreference: 'high-performance',
            }}
            onCreated={({ gl }) => {
                gl.domElement.addEventListener(
                    'webglcontextlost',
                    onContextLost,
                    {
                        once: true,
                    },
                );
            }}
        >
            <ScenePerformanceGovernor
                key={renderQuality}
                active={active}
                quality={renderQuality}
                onPressure={() =>
                    setQualityDrops((current) => Math.min(2, current + 1))
                }
            />
            <ArchiveWorld active={active} quality={renderQuality} />
        </Canvas>
    );
}
