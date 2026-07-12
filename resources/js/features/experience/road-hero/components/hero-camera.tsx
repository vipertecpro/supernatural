import { useFrame } from '@react-three/fiber';
import type { MutableRefObject } from 'react';
import * as THREE from 'three';
import { getRoadHeroPose } from '../motion';
import type { RoadHeroRuntime } from '../types';

export function HeroCamera({
    runtime,
}: {
    runtime: MutableRefObject<RoadHeroRuntime>;
}) {
    const target = new THREE.Vector3();
    const cameraOffset = new THREE.Vector3();
    const lookOffset = new THREE.Vector3();

    useFrame(({ camera }, delta) => {
        const progress = runtime.current.progress;
        const pose = getRoadHeroPose(progress);
        const stop = THREE.MathUtils.smoothstep(progress, 0.94, 1);
        // Hold the camera outside the hairpin so the 180° rotation reads on screen.
        const orbitYaw = pose.yaw - pose.turnArc * 1.15;
        cameraOffset
            .set(pose.turnArc * 1.8, 2.7 + pose.hunt * 0.72 + stop, 7.9)
            .applyAxisAngle(new THREE.Vector3(0, 1, 0), orbitYaw);
        const x = pose.x + cameraOffset.x;
        const y = cameraOffset.y;
        const z = pose.z + cameraOffset.z;

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
        lookOffset
            .set(0, 0.35 + stop * 0.45, -3.7 - pose.hunt)
            .applyAxisAngle(new THREE.Vector3(0, 1, 0), pose.yaw);
        target.set(pose.x + lookOffset.x, lookOffset.y, pose.z + lookOffset.z);
        camera.lookAt(target);
    });

    return null;
}
