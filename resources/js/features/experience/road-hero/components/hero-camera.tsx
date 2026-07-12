import { useFrame } from '@react-three/fiber';
import type { MutableRefObject } from 'react';
import * as THREE from 'three';
import type { RoadHeroRuntime } from '../types';

export function HeroCamera({
    runtime,
}: {
    runtime: MutableRefObject<RoadHeroRuntime>;
}) {
    const target = new THREE.Vector3();

    useFrame(({ camera }, delta) => {
        const progress = runtime.current.progress;
        const first = Math.min(1, progress / 0.3);
        const middle = THREE.MathUtils.clamp((progress - 0.3) / 0.35, 0, 1);
        const final = THREE.MathUtils.clamp((progress - 0.65) / 0.35, 0, 1);
        const x = THREE.MathUtils.lerp(0, -1.7, middle) + final * 3.4;
        const y = 2.7 + first * 0.35 + final * 3.1;
        const z = 7.8 - middle * 1.9 + final * 2.5;

        camera.position.x = THREE.MathUtils.damp(
            camera.position.x,
            x,
            3.4,
            delta,
        );
        camera.position.y = THREE.MathUtils.damp(
            camera.position.y,
            y,
            3.4,
            delta,
        );
        camera.position.z = THREE.MathUtils.damp(
            camera.position.z,
            z,
            3.4,
            delta,
        );
        target.set(0, 0.2 + final * 0.8, -3 - progress * 4.5);
        camera.lookAt(target);
    });

    return null;
}
