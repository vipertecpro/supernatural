import * as THREE from 'three';

export type RoadHeroPose = {
    x: number;
    z: number;
    yaw: number;
    turnPhase: number;
    turnArc: number;
    hunt: number;
    zig: number;
    travelDirection: number;
};

/** Returns the shared vehicle/camera pose for the hairpin and hunt route. */
export function getRoadHeroPose(progress: number): RoadHeroPose {
    const turnPhase = THREE.MathUtils.smoothstep(progress, 0.13, 0.3);
    const turnArc = Math.sin(turnPhase * Math.PI);
    const hunt = THREE.MathUtils.smoothstep(progress, 0.3, 0.94);
    const stop = THREE.MathUtils.smoothstep(progress, 0.94, 1);
    const zig = Math.sin(hunt * Math.PI * 8) * (1 - stop);
    const laneAfterTurn = THREE.MathUtils.smoothstep(progress, 0.27, 0.34);

    return {
        x: turnArc * 4.15 - laneAfterTurn * 1.8 + zig * 2.55 + stop * 1.15,
        z: -turnArc * 2.65 + laneAfterTurn * 0.35,
        yaw: -Math.PI * turnPhase - zig * 0.18,
        turnPhase,
        turnArc,
        hunt,
        zig,
        travelDirection: Math.cos(turnPhase * Math.PI),
    };
}
