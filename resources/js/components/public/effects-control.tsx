import { Check, SlidersHorizontal } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { usePublicEffects } from '@/hooks/use-public-effects';
import type { EffectsPreference } from '@/hooks/use-public-effects';

const options: { value: EffectsPreference; label: string; detail: string }[] = [
    {
        value: 'automatic',
        label: 'Automatic',
        detail: 'Honours system and browser signals',
    },
    {
        value: 'enhanced',
        label: 'Enhanced',
        detail: 'Adds restrained ambient depth',
    },
    {
        value: 'reduced',
        label: 'Reduced',
        detail: 'Uses the static visual treatment',
    },
];

export function EffectsControl() {
    const { preference, effectiveEffects, updatePreference } =
        usePublicEffects();

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    size="icon"
                    aria-label={`Visual effects: ${preference}; currently ${effectiveEffects}`}
                >
                    <SlidersHorizontal />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-64">
                <DropdownMenuLabel>Visual effects</DropdownMenuLabel>
                <DropdownMenuGroup>
                    {options.map((option) => (
                        <DropdownMenuItem
                            key={option.value}
                            onSelect={() => updatePreference(option.value)}
                            className="items-start py-2"
                        >
                            <span className="grid flex-1 gap-0.5">
                                <span>{option.label}</span>
                                <span className="text-xs text-foreground-muted">
                                    {option.detail}
                                </span>
                            </span>
                            {preference === option.value && (
                                <Check className="mt-0.5" aria-hidden="true" />
                            )}
                        </DropdownMenuItem>
                    ))}
                </DropdownMenuGroup>
                <p className="border-t px-2 pt-2 text-xs text-foreground-muted">
                    Reduced motion and data-saver signals always take
                    precedence.
                </p>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
