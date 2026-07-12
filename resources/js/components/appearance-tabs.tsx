import { Monitor, Moon, Sun } from 'lucide-react';
import type { HTMLAttributes } from 'react';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import type { Appearance } from '@/hooks/use-appearance';
import { useAppearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';

export default function AppearanceToggleTab({
    className,
    ...props
}: HTMLAttributes<HTMLDivElement>) {
    const { appearance, updateAppearance } = useAppearance();
    const options = [
        { value: 'light' as Appearance, label: 'Light', icon: Sun },
        { value: 'dark' as Appearance, label: 'Dark', icon: Moon },
        { value: 'system' as Appearance, label: 'System', icon: Monitor },
    ];

    return (
        <div
            className={cn(
                'rounded-lg border bg-surface-primary p-1',
                className,
            )}
            {...props}
        >
            <ToggleGroup
                type="single"
                value={appearance}
                onValueChange={(value) =>
                    value && updateAppearance(value as Appearance)
                }
                className="grid grid-cols-3"
            >
                {options.map(({ value, label, icon: Icon }) => (
                    <ToggleGroupItem
                        key={value}
                        value={value}
                        aria-label={`Use ${label.toLowerCase()} appearance`}
                    >
                        <Icon />
                        {label}
                    </ToggleGroupItem>
                ))}
            </ToggleGroup>
        </div>
    );
}
