import { useFrame } from '@react-three/fiber';
import { useRef } from 'react';
import type { MutableRefObject } from 'react';
import * as THREE from 'three';
import type { RoadHeroRuntime } from '../types';

export function OmenSky({
    runtime,
}: {
    runtime: MutableRefObject<RoadHeroRuntime>;
}) {
    const omen = useRef<THREE.Group>(null);

    useFrame(({ clock }, delta) => {
        const group = omen.current;

        if (!group) {
            return;
        }

        const departure =
            1 -
            THREE.MathUtils.smoothstep(runtime.current.progress, 0.12, 0.34);
        group.rotation.z -= delta * 0.04;
        group.scale.setScalar(0.92 + Math.sin(clock.elapsedTime * 1.3) * 0.025);
        group.visible = departure > 0.02;
        group.traverse((object) => {
            if (
                object instanceof THREE.Mesh &&
                object.material instanceof THREE.Material
            ) {
                object.material.opacity = departure * 0.72;
            }
        });
    });

    return (
        <group
            ref={omen}
            position={[0, 8.5, -46]}
            userData={{ asset: 'procedural-omen-sky' }}
        >
            <mesh>
                <torusGeometry args={[10.5, 0.42, 18, 96]} />
                <meshBasicMaterial
                    color="#b8b8b8"
                    transparent
                    opacity={0.72}
                    blending={THREE.AdditiveBlending}
                    depthWrite={false}
                    toneMapped={false}
                />
            </mesh>
            <mesh rotation-z={0.08} scale={1.05}>
                <torusGeometry args={[10.5, 0.16, 12, 80]} />
                <meshBasicMaterial
                    color="#f0f0f0"
                    transparent
                    opacity={0.45}
                    blending={THREE.AdditiveBlending}
                    depthWrite={false}
                    toneMapped={false}
                />
            </mesh>
            <pointLight color="#c8c8c8" intensity={72} distance={42} />
        </group>
    );
}
