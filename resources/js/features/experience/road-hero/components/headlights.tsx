import * as THREE from 'three';

export function Headlights({ isLight }: { isLight: boolean }) {
    return (
        <group userData={{ asset: 'archive-roadster' }}>
            {[-0.66, 0.66].map((x) => (
                <group key={x} position={[x, 0.05, -2.18]}>
                    <mesh rotation-x={Math.PI / 2} position={[0, 0, -3.6]}>
                        <coneGeometry args={[2.15, 8.4, 32, 1, true]} />
                        <meshBasicMaterial
                            color={isLight ? '#fff0bd' : '#ffe0a0'}
                            transparent
                            opacity={isLight ? 0.035 : 0.095}
                            depthWrite={false}
                            side={THREE.DoubleSide}
                            blending={THREE.AdditiveBlending}
                        />
                    </mesh>
                    <spotLight
                        position={[0, 0, 0]}
                        target-position={[0, -0.8, -15]}
                        intensity={isLight ? 42 : 82}
                        distance={24}
                        angle={0.28}
                        penumbra={0.8}
                        decay={1.7}
                        color="#ffe6ac"
                    />
                    <mesh>
                        <cylinderGeometry args={[0.17, 0.17, 0.08, 24]} />
                        <meshBasicMaterial color="#fff4cc" />
                    </mesh>
                </group>
            ))}
        </group>
    );
}
