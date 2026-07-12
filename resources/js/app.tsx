import { createInertiaApp } from '@inertiajs/react';
import { configureEcho } from '@laravel/echo-react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import PublicMarketingLayout from '@/layouts/public/public-marketing-layout';
import SettingsLayout from '@/layouts/settings/layout';

if (import.meta.env.VITE_REVERB_ENABLED === 'true') {
    configureEcho({ broadcaster: 'reverb' });
}

const appName = import.meta.env.VITE_APP_NAME || 'The Archive';
createInertiaApp({
    title: (title) => (title ? `${title} — ${appName}` : appName),
    layout: (name) => {
        if (name === 'welcome') {
            return PublicMarketingLayout;
        }

        if (name.startsWith('auth/')) {
            return AuthLayout;
        }

        if (name.startsWith('settings/')) {
            return [AppLayout, SettingsLayout];
        }

        return AppLayout;
    },
    strictMode: true,
    withApp(app) {
        return (
            <TooltipProvider delayDuration={250}>
                {app}
                <Toaster />
            </TooltipProvider>
        );
    },
    progress: { color: '#78909c', showSpinner: false },
});
initializeTheme();
