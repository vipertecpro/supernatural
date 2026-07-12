import { useFrame } from '@react-three/fiber';
import { useLayoutEffect, useMemo, useRef } from 'react';
import type { MutableRefObject } from 'react';
import * as THREE from 'three';
import type { ExperienceQuality } from '../../types';
import type { RoadHeroRuntime } from '../types';

export function Forest({
    runtime,
    quality,
    isLight,
}: {
    runtime: MutableRefObject<RoadHeroRuntime>;
    quality: Exclude<ExperienceQuality, 'fallback'>;
    isLight: boolean;
}) {
    const count = quality === 'high' ? 86 : quality === 'medium' ? 58 : 34;
    const crowns = useRef<THREE.InstancedMesh>(null);
    const trunks = useRef<THREE.InstancedMesh>(null);
    const forest = useRef<THREE.Group>(null);
    const placements = useMemo(
        () =>
            Array.from({ length: count }, (_, index) => {
                const side = index % 2 === 0 ? -1 : 1;
                const lane = Math.floor(index / 2);
                const variance = Math.sin(index * 91.17) * 1.4;

                return {
                    x: side * (7.3 + (index % 5) * 1.35 + variance),
                    z: 10 - lane * 3.8,
                    height: 3.8 + ((index * 7) % 9) * 0.42,
                    scale: 0.72 + ((index * 13) % 7) * 0.08,
                };
            }),
        [count],
    );

    useLayoutEffect(() => {
        const matrix = new THREE.Matrix4();

        placements.forEach(({ x, z, height, scale }, index) => {
            matrix.compose(
                new THREE.Vector3(x, height * 0.48 - 0.6, z),
                new THREE.Quaternion(),
                new THREE.Vector3(scale, height, scale),
            );
            crowns.current?.setMatrixAt(index, matrix);
            matrix.compose(
                new THREE.Vector3(x, height * 0.14 - 0.55, z),
                new THREE.Quaternion(),
                new THREE.Vector3(0.16 * scale, height * 0.3, 0.16 * scale),
            );
            trunks.current?.setMatrixAt(index, matrix);
        });

        if (crowns.current) {
            crowns.current.instanceMatrix.needsUpdate = true;
        }

        if (trunks.current) {
            trunks.current.instanceMatrix.needsUpdate = true;
        }
    }, [placements]);

    useFrame(({ clock }, delta) => {
        if (!forest.current || !runtime.current.active) {
            return;
        }

        const target = (runtime.current.progress * 126) % 7.6;
        forest.current.position.z = THREE.MathUtils.damp(
            forest.current.position.z,
            target,
            5,
            delta,
        );
        forest.current.rotation.z = Math.sin(clock.elapsedTime * 0.23) * 0.0015;
    });

    return (
        <group ref={forest} userData={{ asset: 'forest-silhouettes' }}>
            <instancedMesh ref={crowns} args={[undefined, undefined, count]}>
                <coneGeometry args={[1, 1, 7]} />
                <meshStandardMaterial
                    color={isLight ? '#33433a' : '#07100d'}
                    roughness={0.92}
                />
            </instancedMesh>
            <instancedMesh ref={trunks} args={[undefined, undefined, count]}>
                <cylinderGeometry args={[1, 1.25, 1, 6]} />
                <meshStandardMaterial
                    color={isLight ? '#4b4136' : '#120f0d'}
                    roughness={1}
                />
            </instancedMesh>
        </group>
    );
}
