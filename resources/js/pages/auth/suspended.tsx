import { Form, Head } from '@inertiajs/react';
import { ShieldAlert } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { logout } from '@/routes';

export default function Suspended({
    reason,
    effectiveAt,
    expiresAt,
}: {
    reason: string;
    effectiveAt: string | null;
    expiresAt: string | null;
}) {
    return (
        <>
            <Head title="Account access suspended" />
            <div className="space-y-6">
                <div className="rounded-xl border border-danger bg-surface-secondary p-5">
                    <ShieldAlert className="size-6 text-danger" />
                    <h2 className="mt-3 font-medium">
                        Platform access is unavailable
                    </h2>
                    <p className="mt-2 text-sm text-foreground-secondary">
                        {reason}
                    </p>
                    <dl className="text-metadata mt-4 grid gap-1 text-foreground-evidence">
                        {effectiveAt && (
                            <div>
                                <dt className="inline">Effective: </dt>
                                <dd className="inline">{effectiveAt}</dd>
                            </div>
                        )}
                        <div>
                            <dt className="inline">Duration: </dt>
                            <dd className="inline">
                                {expiresAt ??
                                    'No scheduled expiry is available'}
                            </dd>
                        </div>
                    </dl>
                </div>
                <p className="text-sm text-foreground-muted">
                    Internal case notes, reporter identities, and moderation
                    evidence are not shown here. Eligible appeal and mandatory
                    notification access remains available through the existing
                    API contract.
                </p>
                <Form {...logout.form()}>
                    {({ processing }) => (
                        <Button
                            className="w-full"
                            variant="outline"
                            disabled={processing}
                        >
                            Sign out
                        </Button>
                    )}
                </Form>
            </div>
        </>
    );
}

Suspended.layout = {
    title: 'Account access suspended',
    description:
        'Review the public-safe account restriction information below.',
};
