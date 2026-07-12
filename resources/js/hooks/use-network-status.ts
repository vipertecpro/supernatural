import { useSyncExternalStore } from 'react';

const subscribe = (notify: () => void): (() => void) => {
    window.addEventListener('online', notify);
    window.addEventListener('offline', notify);

    return () => {
        window.removeEventListener('online', notify);
        window.removeEventListener('offline', notify);
    };
};

const getNetworkSnapshot = (): boolean => navigator.onLine;

export function useNetworkStatus() {
    const isOnline = useSyncExternalStore(
        subscribe,
        getNetworkSnapshot,
        () => true,
    );

    return { isOnline } as const;
}
