import { Canvas, useFrame } from '@react-three/fiber';
import { useMemo, useRef, useState } from 'react';
import * as THREE from 'three';
import type { PublicSceneVariant } from './public-scene-variants';
import {
    applyExperienceQualityDrops,
    ScenePerformanceGovernor,
} from './scene-performance-governor';
import type { ExperienceQuality } from './types';

const vertexShader = `
    varying vec2 vUv;
    void main() {
        vUv = uv;
        gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
    }
`;

const fragmentShader = `
    uniform float uTime;
    uniform vec3 uAccent;
    varying vec2 vUv;

    float line(float value, float width) {
        return smoothstep(width, 0.0, abs(value));
    }

    void main() {
        vec2 uv = vUv - 0.5;
        float radius = length(uv);
        float wave = sin(radius * 42.0 - uTime * 1.2) * 0.5 + 0.5;
        float gridX = line(fract(vUv.x * 18.0 + uTime * 0.018) - 0.5, 0.035);
        float gridY = line(fract(vUv.y * 12.0 - uTime * 0.025) - 0.5, 0.028);
        float signal = (gridX + gridY) * 0.08 + wave * 0.055;
        float vignette = smoothstep(0.8, 0.08, radius);
        gl_FragColor = vec4(uAccent * signal * vignette, signal * vignette);
    }
`;

const accents: Record<PublicSceneVariant, string> = {
    archive: '#7bd4c7',
    knowledge: '#8bd5c8',
    system: '#74bfe8',
    signal: '#c1dfd8',
    boundary: '#d1a974',
    rights: '#d58c72',
};

function AmbientWorld({
    active,
    quality,
    variant,
}: {
    active: boolean;
    quality: ExperienceQuality;
    variant: PublicSceneVariant;
}) {
    const rig = useRef<THREE.Group>(null);
    const core = useRef<THREE.Group>(null);
    const records = useRef<THREE.Group>(null);
    const shader = useRef<THREE.ShaderMaterial>(null);
    const accent = accents[variant];
    const particles = useMemo(() => {
        const count =
            quality === 'high' ? 1100 : quality === 'medium' ? 620 : 280;
        const positions = new Float32Array(count * 3);

        for (let index = 0; index < count; index += 1) {
            const seed = Math.sin(index * 78.233) * 43758.5453;
            const noise = seed - Math.floor(seed);
            positions[index * 3] = (noise - 0.5) * 28;
            positions[index * 3 + 1] = ((index * 0.618) % 1) * 15 - 7;
            positions[index * 3 + 2] = -4 - ((index * 0.347) % 1) * 20;
        }

        return positions;
    }, [quality]);

    useFrame(({ clock, pointer }, delta) => {
        if (!active || document.hidden) {
            return;
        }

        const elapsed = clock.getElapsedTime();

        if (shader.current) {
            shader.current.uniforms.uTime.value = elapsed;
        }

        if (rig.current) {
            rig.current.rotation.y = THREE.MathUtils.lerp(
                rig.current.rotation.y,
                pointer.x * 0.16,
                Math.min(1, delta * 1.5),
            );
            rig.current.rotation.x = THREE.MathUtils.lerp(
                rig.current.rotation.x,
                pointer.y * -0.08,
                Math.min(1, delta * 1.5),
            );
        }

        if (core.current) {
            core.current.rotation.x = elapsed * 0.08;
            core.current.rotation.y = elapsed * 0.13;
            core.current.scale.setScalar(1 + Math.sin(elapsed * 0.7) * 0.045);
        }

        records.current?.children.forEach((record, index) => {
            record.position.y = ((index * 1.8 + elapsed * 0.22) % 12) - 6;
            record.rotation.z = Math.sin(elapsed * 0.24 + index) * 0.18;
        });
    });

    return (
        <>
            <fog attach="fog" args={['#030708', 7, 28]} />
            <ambientLight intensity={0.25} color={accent} />
            <pointLight position={[4, 4, 2]} intensity={12} color={accent} />
            <mesh position={[0, 0, -12]} scale={[24, 15, 1]}>
                <planeGeometry args={[1, 1, 1, 1]} />
                <shaderMaterial
                    ref={shader}
                    transparent
                    depthWrite={false}
                    blending={THREE.AdditiveBlending}
                    uniforms={{
                        uTime: { value: 0 },
                        uAccent: { value: new THREE.Color(accent) },
                    }}
                    vertexShader={vertexShader}
                    fragmentShader={fragmentShader}
                />
            </mesh>
            <group ref={rig} position={[3.7, 0.2, -7]}>
                <group ref={core}>
                    <mesh>
                        <icosahedronGeometry
                            args={[2.2, quality === 'high' ? 2 : 1]}
                        />
                        <meshBasicMaterial
                            color={accent}
                            wireframe
                            transparent
                            opacity={0.32}
                            blending={THREE.AdditiveBlending}
                        />
                    </mesh>
                    {[2.8, 3.6, 4.5].map((radius, index) => (
                        <mesh
                            key={radius}
                            rotation={[index * 0.7, index * 0.35, index * 0.8]}
                        >
                            <torusGeometry args={[radius, 0.018, 6, 96]} />
                            <meshBasicMaterial
                                color={accent}
                                transparent
                                opacity={0.28 - index * 0.055}
                                blending={THREE.AdditiveBlending}
                            />
                        </mesh>
                    ))}
                </group>
                <group ref={records}>
                    {Array.from({ length: quality === 'low' ? 5 : 11 }).map(
                        (_, index) => (
                            <mesh
                                key={index}
                                position={[
                                    -8 + (index % 5) * 3.6,
                                    -5 + (index % 6) * 1.8,
                                    -2 - (index % 4) * 1.2,
                                ]}
                                rotation={[0.08, index * 0.12, 0]}
                            >
                                <planeGeometry args={[1.4, 1.9]} />
                                <meshBasicMaterial
                                    color={accent}
                                    transparent
                                    opacity={0.08 + (index % 3) * 0.025}
                                    side={THREE.DoubleSide}
                                />
                            </mesh>
                        ),
                    )}
                </group>
            </group>
            <points>
                <bufferGeometry>
                    <bufferAttribute
                        attach="attributes-position"
                        args={[particles, 3]}
                    />
                </bufferGeometry>
                <pointsMaterial
                    color={accent}
                    size={quality === 'high' ? 0.035 : 0.025}
                    transparent
                    opacity={0.48}
                    depthWrite={false}
                />
            </points>
        </>
    );
}

export default function AmbientArchiveScene({
    active,
    quality,
    variant,
    onContextLost,
}: {
    active: boolean;
    quality: ExperienceQuality;
    variant: PublicSceneVariant;
    onContextLost: () => void;
}) {
    const [qualityDrops, setQualityDrops] = useState(0);
    const renderQuality = applyExperienceQualityDrops(quality, qualityDrops);

    return (
        <Canvas
            camera={{ position: [0, 0, 7], fov: 48 }}
            dpr={renderQuality === 'high' ? [1, 1.5] : [1, 1.2]}
            frameloop={active ? 'always' : 'never'}
            gl={{
                antialias: renderQuality !== 'low',
                alpha: true,
                powerPreference: 'high-performance',
            }}
            onCreated={({ gl }) => {
                gl.domElement.addEventListener(
                    'webglcontextlost',
                    onContextLost,
                    { once: true },
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
            <AmbientWorld
                active={active}
                quality={renderQuality}
                variant={variant}
            />
        </Canvas>
    );
}
