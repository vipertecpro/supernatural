import { AlertCircle } from 'lucide-react';
import { useEffect, useMemo, useRef } from 'react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';

export function FormErrorSummary({
    errors,
}: {
    errors: Record<string, string>;
}) {
    const summaryRef = useRef<HTMLDivElement>(null);
    const messages = useMemo(
        () => Array.from(new Set(Object.values(errors).filter(Boolean))),
        [errors],
    );
    const fingerprint = messages.join('|');

    useEffect(() => {
        if (fingerprint) {
            summaryRef.current?.focus();
        }
    }, [fingerprint]);

    if (messages.length === 0) {
        return null;
    }

    return (
        <Alert ref={summaryRef} tabIndex={-1} variant="destructive">
            <AlertCircle />
            <AlertTitle>Review the highlighted fields</AlertTitle>
            <AlertDescription>
                <ul className="mt-2 list-disc space-y-1 pl-5">
                    {messages.map((message) => (
                        <li key={message}>{message}</li>
                    ))}
                </ul>
            </AlertDescription>
        </Alert>
    );
}
