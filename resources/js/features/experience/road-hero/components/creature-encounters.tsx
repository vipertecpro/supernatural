/* eslint-disable react-hooks/immutability -- R3F frame callbacks intentionally animate Three.js uniforms and scene objects. */
import { useTexture } from '@react-three/drei';
import { useFrame } from '@react-three/fiber';
import { useEffect, useMemo, useRef } from 'react';
import type { MutableRefObject, ReactNode } from 'react';
import * as THREE from 'three';
import { getRoadHeroPose } from '../motion';
import type { RoadHeroRuntime } from '../types';

type EncounterProps = {
    runtime: MutableRefObject<RoadHeroRuntime>;
    start: number;
    x: number;
    children: ReactNode;
};

function Encounter({ runtime, start, x, children }: EncounterProps) {
    const creature = useRef<THREE.Group>(null);

    useFrame(({ clock }) => {
        const group = creature.current;

        if (!group) {
            return;
        }

        const progress = runtime.current.progress;
        const pose = getRoadHeroPose(progress);
        const arrival = THREE.MathUtils.smoothstep(
            progress,
            start - 0.075,
            start - 0.018,
        );
        const impact = THREE.MathUtils.smoothstep(
            progress,
            start + 0.008,
            start + 0.055,
        );
        const opacity = arrival * (1 - impact);
        const direction = pose.travelDirection < 0 ? 1 : -1;
        group.visible = opacity > 0.01;
        group.position.x =
            pose.x + x + Math.sin(clock.elapsedTime * 1.8 + start) * 0.18;
        group.position.y =
            0.55 + Math.sin(clock.elapsedTime * 2.3) * 0.16 + impact * 2.5;
        group.position.z =
            pose.z + direction * (7 - arrival * 3.4 - impact * 1.2);
        group.rotation.y =
            (direction > 0 ? Math.PI : 0) +
            Math.sin(clock.elapsedTime * 0.8) * 0.2 +
            impact * 1.8;
        group.rotation.z = impact * 0.55;
        group.scale.setScalar(0.4 + arrival * 0.85 + impact * 0.75);
        group.traverse((object) => {
            if (!(object instanceof THREE.Mesh)) {
                return;
            }

            const material = object.material;

            if (material instanceof THREE.Material) {
                material.opacity = opacity;
                material.transparent = true;
            }
        });
    });

    return (
        <group ref={creature} position={[x, 0.55, -7]} visible={false}>
            {children}
        </group>
    );
}

const ghostVertexShader = `
    uniform float uTime;
    varying vec3 vPosition;

    void main() {
        vPosition = position;
        vec3 displaced = position;
        displaced.x += sin(position.y * 5.0 + uTime * 2.2) * 0.025;
        displaced.z += cos(position.y * 4.0 - uTime * 1.7) * 0.018;
        gl_Position = projectionMatrix * modelViewMatrix * vec4(displaced, 1.0);
    }
`;

const ghostFragmentShader = `
    uniform float uTime;
    uniform float uOpacity;
    uniform float uDissolve;
    varying vec3 vPosition;

    float spectralNoise(vec3 point) {
        float waveA = sin(point.y * 8.5 + uTime * 3.1);
        float waveB = sin(point.x * 13.0 - point.z * 9.0 - uTime * 2.3);
        float waveC = cos((point.x + point.y + point.z) * 17.0 + uTime);
        return 0.5 + (waveA + waveB + waveC) / 6.0;
    }

    void main() {
        float noise = spectralNoise(vPosition);
        float breakup = smoothstep(uDissolve - 0.18, uDissolve + 0.2, noise);
        float pulse = 0.78 + sin(uTime * 4.0 + vPosition.y * 3.0) * 0.12;
        vec3 shadow = vec3(0.035, 0.16, 0.18);
        vec3 glow = vec3(0.38, 0.9, 0.86);
        vec3 color = mix(shadow, glow, noise * pulse);
        float alpha = uOpacity * breakup * (0.3 + noise * 0.42);

        if (alpha < 0.018) {
            discard;
        }

        gl_FragColor = vec4(color, alpha);
    }
`;

function GhostSurface({ material }: { material: THREE.ShaderMaterial }) {
    return <primitive object={material} attach="material" />;
}

function SpectralRunner({
    runtime,
    side,
    delay,
}: {
    runtime: MutableRefObject<RoadHeroRuntime>;
    side: -1 | 1;
    delay: number;
}) {
    const runner = useRef<THREE.Group>(null);
    const leftArm = useRef<THREE.Group>(null);
    const rightArm = useRef<THREE.Group>(null);
    const leftLeg = useRef<THREE.Group>(null);
    const rightLeg = useRef<THREE.Group>(null);
    const ribbons = useRef<THREE.Group>(null);
    const aura = useRef<THREE.PointLight>(null);
    const ghostTexture = useTexture(
        '/media/road-journey/generated/spectral-runner-768.png',
    );
    const ghostMaterial = useMemo(
        () =>
            new THREE.ShaderMaterial({
                uniforms: {
                    uTime: { value: 0 },
                    uOpacity: { value: 0 },
                    uDissolve: { value: 0 },
                },
                vertexShader: ghostVertexShader,
                fragmentShader: ghostFragmentShader,
                transparent: true,
                depthWrite: false,
                side: THREE.DoubleSide,
                blending: THREE.NormalBlending,
                toneMapped: false,
            }),
        [],
    );

    useEffect(() => {
        ghostTexture.colorSpace = THREE.SRGBColorSpace;
        ghostTexture.needsUpdate = true;

        return () => ghostMaterial.dispose();
    }, [ghostMaterial, ghostTexture]);

    useFrame(({ clock }) => {
        const ghost = runner.current;

        if (!ghost) {
            return;
        }

        const progress = runtime.current.progress;
        const pose = getRoadHeroPose(progress);
        const chapter =
            THREE.MathUtils.smoothstep(progress, 0.305, 0.335) *
            (1 - THREE.MathUtils.smoothstep(progress, 0.455, 0.49));
        const cycle = (clock.elapsedTime * 0.19 + delay) % 1;
        const emerge = THREE.MathUtils.smoothstep(cycle, 0.03, 0.17);
        const sprint = THREE.MathUtils.smoothstep(cycle, 0.12, 0.73);
        const impact = THREE.MathUtils.smoothstep(cycle, 0.7, 0.88);
        const opacity = chapter * emerge * (1 - impact);
        const stride = clock.elapsedTime * 11.5 + delay * Math.PI * 2;
        const startX = side * (8.4 + delay * 1.8);
        const startZ = pose.z + 10.5 + delay * 2.2;
        const targetX = pose.x + side * 0.32;
        const targetZ = pose.z + 2.75;

        ghost.visible = opacity > 0.015;
        ghost.position.x = THREE.MathUtils.lerp(startX, targetX, sprint);
        ghost.position.z = THREE.MathUtils.lerp(startZ, targetZ, sprint);
        ghost.position.y =
            -0.38 + Math.abs(Math.sin(stride)) * 0.16 + impact * 0.35;
        ghost.rotation.y = Math.atan2(targetX - startX, targetZ - startZ);
        ghost.rotation.z = side * -0.1 + Math.sin(stride * 0.5) * 0.025;
        ghost.scale.setScalar(0.82 + emerge * 0.2 + impact * 0.35);
        ghostMaterial.uniforms.uTime.value = clock.elapsedTime + delay * 4;
        ghostMaterial.uniforms.uOpacity.value = opacity * 0.08;
        ghostMaterial.uniforms.uDissolve.value = impact * 0.9;

        if (leftArm.current && rightArm.current) {
            leftArm.current.rotation.x = Math.sin(stride) * 0.85 - 0.25;
            rightArm.current.rotation.x = -Math.sin(stride) * 0.85 - 0.25;
        }

        if (leftLeg.current && rightLeg.current) {
            leftLeg.current.rotation.x = -Math.sin(stride) * 0.78;
            rightLeg.current.rotation.x = Math.sin(stride) * 0.78;
        }

        if (ribbons.current) {
            ribbons.current.rotation.z =
                Math.sin(clock.elapsedTime * 3.1 + delay) * 0.12;
            ribbons.current.position.z = 0.25 + sprint * 0.4;
        }

        ghost.traverse((object) => {
            if (
                !(object instanceof THREE.Mesh) &&
                !(object instanceof THREE.Sprite)
            ) {
                return;
            }

            const material = object.material;

            if (object instanceof THREE.Sprite) {
                material.opacity =
                    opacity * Number(object.userData.opacityWeight ?? 1);
                material.rotation =
                    Math.sin(stride * 0.5) * 0.018 + side * 0.025;
            }

            if (material instanceof THREE.MeshBasicMaterial) {
                material.transparent = true;
                material.opacity =
                    opacity *
                    (0.34 +
                        Math.sin(
                            clock.elapsedTime * 7 + object.position.y * 3,
                        ) *
                            0.1);
            }
        });

        if (aura.current) {
            aura.current.intensity = opacity * (11 + Math.sin(stride) * 2);
        }
    });

    return (
        <group ref={runner} visible={false}>
            {[
                { x: 0, z: 0, weight: 0.82, scale: 1 },
                { x: side * 0.16, z: 0.22, weight: 0.18, scale: 0.97 },
                { x: side * 0.3, z: 0.42, weight: 0.08, scale: 0.93 },
            ].map((echo) => (
                <sprite
                    key={echo.z}
                    position={[echo.x, 1.75, echo.z]}
                    scale={[1.9 * echo.scale, 3.35 * echo.scale, 1]}
                    userData={{ opacityWeight: echo.weight }}
                >
                    <spriteMaterial
                        map={ghostTexture}
                        color="#d5d5d5"
                        transparent
                        opacity={0}
                        depthWrite={false}
                        toneMapped={false}
                    />
                </sprite>
            ))}
            <mesh position={[0, 2.5, 0]} scale={[0.78, 1, 0.72]}>
                <sphereGeometry args={[0.31, 20, 16]} />
                <GhostSurface material={ghostMaterial} />
            </mesh>
            <mesh position={[0, 1.65, 0]} scale={[0.92, 1.15, 0.62]}>
                <capsuleGeometry args={[0.38, 0.9, 8, 18]} />
                <GhostSurface material={ghostMaterial} />
            </mesh>
            <mesh position={[0, 1.28, 0.12]} rotation-x={0.1}>
                <coneGeometry args={[0.78, 1.65, 24, 2, true]} />
                <GhostSurface material={ghostMaterial} />
            </mesh>

            {[
                { ref: leftArm, x: -0.48, z: 0.02 },
                { ref: rightArm, x: 0.48, z: 0.02 },
            ].map((arm) => (
                <group
                    key={arm.x}
                    ref={arm.ref}
                    position={[arm.x, 2.03, arm.z]}
                    rotation-z={arm.x * -0.28}
                >
                    <mesh position={[0, -0.48, 0]}>
                        <capsuleGeometry args={[0.12, 0.72, 6, 10]} />
                        <GhostSurface material={ghostMaterial} />
                    </mesh>
                </group>
            ))}

            {[
                { ref: leftLeg, x: -0.24 },
                { ref: rightLeg, x: 0.24 },
            ].map((leg) => (
                <group key={leg.x} ref={leg.ref} position={[leg.x, 1.1, 0]}>
                    <mesh position={[0, -0.58, 0]}>
                        <capsuleGeometry args={[0.14, 0.86, 6, 10]} />
                        <GhostSurface material={ghostMaterial} />
                    </mesh>
                </group>
            ))}

            <group ref={ribbons}>
                {[-0.42, -0.14, 0.14, 0.42].map((x, index) => (
                    <mesh
                        key={x}
                        position={[x, 1.12 - index * 0.08, 0.28]}
                        rotation-x={-0.18 - index * 0.04}
                        rotation-z={x * 0.18}
                    >
                        <planeGeometry args={[0.28, 2.3 + index * 0.22]} />
                        <meshBasicMaterial
                            color={index % 2 === 0 ? '#a8a8a8' : '#e0e0e0'}
                            transparent
                            opacity={0}
                            blending={THREE.AdditiveBlending}
                            depthWrite={false}
                            side={THREE.DoubleSide}
                            toneMapped={false}
                        />
                    </mesh>
                ))}
            </group>
            {[-0.11, 0.11].map((x) => (
                <mesh key={x} position={[x, 2.54, 0.28]}>
                    <sphereGeometry args={[0.025, 8, 8]} />
                    <meshBasicMaterial
                        color="#f2f2f2"
                        transparent
                        opacity={0}
                        toneMapped={false}
                    />
                </mesh>
            ))}
            <pointLight
                ref={aura}
                position={[0, 1.5, 0.3]}
                color="#b8b8b8"
                intensity={0}
                distance={5}
            />
        </group>
    );
}

function GhostRush({
    runtime,
}: {
    runtime: MutableRefObject<RoadHeroRuntime>;
}) {
    return (
        <group userData={{ encounter: 'tree-line-ghost-rush' }}>
            <SpectralRunner runtime={runtime} side={1} delay={0} />
            <SpectralRunner runtime={runtime} side={-1} delay={0.36} />
            <SpectralRunner runtime={runtime} side={1} delay={0.7} />
        </group>
    );
}

function Eyes({ color = '#f0f0f0' }: { color?: string }) {
    return [-0.16, 0.16].map((x) => (
        <mesh key={x} position={[x, 2.35, 0.42]}>
            <sphereGeometry args={[0.055, 10, 10]} />
            <meshBasicMaterial color={color} toneMapped={false} />
        </mesh>
    ));
}

export function CreatureEncounters({
    runtime,
}: {
    runtime: MutableRefObject<RoadHeroRuntime>;
}) {
    return (
        <group userData={{ asset: 'procedural-creature-encounters' }}>
            <GhostRush runtime={runtime} />

            <Encounter runtime={runtime} start={0.55} x={-2.5}>
                <mesh position={[0, 1.1, 0]}>
                    <capsuleGeometry args={[0.58, 1.65, 8, 16]} />
                    <meshStandardMaterial
                        color="#080808"
                        roughness={0.82}
                        emissive="#242424"
                        emissiveIntensity={0.8}
                    />
                </mesh>
                <mesh position={[0, 2.25, 0]}>
                    <sphereGeometry args={[0.48, 18, 14]} />
                    <meshStandardMaterial color="#111111" roughness={0.9} />
                </mesh>
                {[-0.26, 0.26].map((x) => (
                    <mesh key={x} position={[x, 2.78, 0]} rotation-z={x * -1.8}>
                        <coneGeometry args={[0.12, 0.7, 8]} />
                        <meshStandardMaterial color="#161616" roughness={1} />
                    </mesh>
                ))}
                <Eyes />
            </Encounter>

            <Encounter runtime={runtime} start={0.71} x={2.35}>
                <mesh position={[0, 1.05, 0]}>
                    <capsuleGeometry args={[0.64, 1.7, 8, 18]} />
                    <meshStandardMaterial color="#0b0b0b" roughness={0.78} />
                </mesh>
                <mesh position={[0, 2.3, 0]}>
                    <sphereGeometry args={[0.44, 20, 16]} />
                    <meshStandardMaterial
                        color="#b5b5b5"
                        roughness={0.72}
                        emissive="#292929"
                        emissiveIntensity={0.45}
                    />
                </mesh>
                <Eyes color="#ededed" />
                {[-0.1, 0.1].map((x) => (
                    <mesh key={x} position={[x, 2.04, 0.43]} rotation-x={0.2}>
                        <coneGeometry args={[0.035, 0.2, 6]} />
                        <meshBasicMaterial color="#eeeeee" />
                    </mesh>
                ))}
            </Encounter>

            <Encounter runtime={runtime} start={0.87} x={-2.2}>
                {[0.65, 1, 1.35].map((scale, index) => (
                    <mesh key={scale} position={[0, 1.45, 0]} scale={scale}>
                        <torusGeometry args={[0.72, 0.08, 12, 32]} />
                        <meshBasicMaterial
                            color={index === 1 ? '#eeeeee' : '#929292'}
                            blending={THREE.AdditiveBlending}
                            depthWrite={false}
                        />
                    </mesh>
                ))}
                <pointLight
                    position={[0, 1.45, 0.4]}
                    color="#d5d5d5"
                    intensity={22}
                    distance={8}
                />
            </Encounter>
        </group>
    );
}
