import { Component } from 'react';
import type { ReactNode } from 'react';

export class SceneErrorBoundary extends Component<
    { children: ReactNode; fallback: ReactNode; onError: () => void },
    { failed: boolean }
> {
    public state = { failed: false };

    public static getDerivedStateFromError(): { failed: boolean } {
        return { failed: true };
    }

    public componentDidCatch(): void {
        this.props.onError();
    }

    public render(): ReactNode {
        return this.state.failed ? this.props.fallback : this.props.children;
    }
}
