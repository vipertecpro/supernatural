import { Form, Head } from '@inertiajs/react';
import { Route } from 'lucide-react';
import { update } from '@/actions/App/Http/Controllers/Onboarding/ViewingOrderController';
import { FormErrorSummary } from '@/components/forms/form-error-summary';
import { EmptyState } from '@/components/states/state-panel';
import { Badge } from '@/components/ui/badge';
import {
    OnboardingSelectionCard,
    OnboardingStepActions,
    OnboardingStepHeader,
} from '@/features/onboarding/components/onboarding-step';
import type { OnboardingPageProps } from '@/features/onboarding/types';

type ViewingOrder = {
    id: number;
    universeId: number;
    universeName: string;
    name: string;
    description: string | null;
    type: string;
    locale: string;
    itemCount: number;
    isDefault: boolean;
};

export default function ViewingOrderPage({
    onboarding,
    orders,
    selectedOrderId,
}: OnboardingPageProps & {
    orders: ViewingOrder[];
    selectedOrderId: number | null;
}) {
    return (
        <>
            <Head title="Preferred viewing order" />
            <OnboardingStepHeader
                eyebrow="Step 5"
                title="Choose a preferred viewing order"
                description="Only published, public, non-archived orders for your selected universes appear here. Choosing one does not create a Journey."
            />

            <Form {...update.form()} className="mt-8">
                {({ processing, errors }) => (
                    <div className="space-y-6">
                        <FormErrorSummary errors={errors} />
                        <input
                            type="hidden"
                            name="expected_version"
                            value={onboarding.version}
                        />

                        {orders.length === 0 ? (
                            <>
                                <input
                                    type="hidden"
                                    name="viewing_order_id"
                                    value=""
                                />
                                <EmptyState
                                    icon={Route}
                                    title="No published viewing orders yet"
                                    description="Continue without a preference. The Archive will not invent or imply an official order."
                                />
                            </>
                        ) : (
                            <fieldset>
                                <legend className="sr-only">
                                    Select a preferred viewing order
                                </legend>
                                <div className="grid gap-3">
                                    {orders.map((order) => (
                                        <label
                                            key={order.id}
                                            className="cursor-pointer focus-within:ring-[3px] focus-within:ring-ring"
                                        >
                                            <OnboardingSelectionCard>
                                                <span className="flex gap-3">
                                                    <input
                                                        type="radio"
                                                        name="viewing_order_id"
                                                        value={order.id}
                                                        defaultChecked={
                                                            selectedOrderId ===
                                                            order.id
                                                        }
                                                        className="accent-action mt-1 size-5"
                                                    />
                                                    <span className="min-w-0 flex-1">
                                                        <span className="flex flex-wrap items-center gap-2 font-medium">
                                                            {order.name}
                                                            {order.isDefault && (
                                                                <Badge variant="secondary">
                                                                    Recommended
                                                                    default
                                                                </Badge>
                                                            )}
                                                        </span>
                                                        <span className="mt-1 block text-sm text-foreground-muted">
                                                            {order.universeName}
                                                            {order.description
                                                                ? ` — ${order.description}`
                                                                : ''}
                                                        </span>
                                                        <span className="text-metadata mt-3 block text-foreground-evidence">
                                                            {order.type} ·{' '}
                                                            {order.itemCount}{' '}
                                                            items ·{' '}
                                                            {order.locale}
                                                        </span>
                                                    </span>
                                                </span>
                                            </OnboardingSelectionCard>
                                        </label>
                                    ))}
                                    <label className="rounded-xl border border-border p-4">
                                        <input
                                            type="radio"
                                            name="viewing_order_id"
                                            value=""
                                            defaultChecked={
                                                selectedOrderId === null
                                            }
                                            className="accent-action mr-3 size-5"
                                        />
                                        No preference for now
                                    </label>
                                </div>
                            </fieldset>
                        )}

                        <OnboardingStepActions
                            backHref={onboarding.backHref}
                            processing={processing}
                        />
                    </div>
                )}
            </Form>
        </>
    );
}
