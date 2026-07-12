export function Headlights({ isLight }: { isLight: boolean }) {
    return (
        <group userData={{ asset: 'archive-roadster' }}>
            {[-0.66, 0.66].map((x) => (
                <group key={x} position={[x, 0.05, -2.18]}>
                    <spotLight
                        position={[0, 0, 0]}
                        target-position={[0, -0.8, -15]}
                        intensity={isLight ? 42 : 82}
                        distance={24}
                        angle={0.28}
                        penumbra={0.8}
                        decay={1.7}
                        color="#e6e6e6"
                    />
                    <pointLight
                        intensity={isLight ? 2.5 : 5.5}
                        distance={4.5}
                        decay={2}
                        color="#e6e6e6"
                    />
                    <mesh>
                        <cylinderGeometry args={[0.17, 0.17, 0.08, 24]} />
                        <meshBasicMaterial color="#f4f4f4" />
                    </mesh>
                </group>
            ))}
        </group>
    );
}
