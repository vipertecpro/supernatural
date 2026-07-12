import { Link } from '@inertiajs/react';
import { ChevronsUpDown } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { WorkspaceDestination } from '@/types';

export function WorkspaceSwitcher({
    workspaces,
}: {
    workspaces: WorkspaceDestination[];
}) {
    if (workspaces.length === 0) {
        return null;
    }

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="outline" size="sm">
                    Workspaces
                    <ChevronsUpDown data-icon="inline-end" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuLabel>Switch context</DropdownMenuLabel>
                <DropdownMenuGroup>
                    {workspaces.map((workspace) => (
                        <DropdownMenuItem key={workspace.key} asChild>
                            <Link href={workspace.href}>{workspace.label}</Link>
                        </DropdownMenuItem>
                    ))}
                </DropdownMenuGroup>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
