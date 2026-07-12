import { WifiOff } from 'lucide-react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { useNetworkStatus } from '@/hooks/use-network-status';

export function OfflineBanner() {
    const { isOnline } = useNetworkStatus();

    if (isOnline) {
        return null;
    }

    return (
        <Alert
            role="status"
            aria-live="polite"
            className="rounded-none border-x-0 border-offline bg-surface-secondary"
        >
            <WifiOff />
            <AlertTitle>You are offline</AlertTitle>
            <AlertDescription>
                Existing information may be stale. Changes will not be queued
                automatically.
            </AlertDescription>
        </Alert>
    );
}
