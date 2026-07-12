import '@fontsource/instrument-sans/latin-400.css';
import '@fontsource/instrument-sans/latin-500.css';
import '@fontsource/instrument-sans/latin-600.css';
import '@fontsource/cormorant-garamond/latin-500.css';
import '@fontsource/cormorant-garamond/latin-600.css';
import '@fontsource/cinzel-decorative/latin-700.css';
import '@fontsource/special-elite/latin-400.css';
import { createInertiaApp } from '@inertiajs/react';
import { configureEcho } from '@laravel/echo-react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { ExperienceProvider } from '@/features/experience/experience-provider';
import { initializeTheme } from '@/hooks/use-appearance';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import OnboardingLayout from '@/layouts/onboarding/onboarding-layout';
import PublicContentLayout from '@/layouts/public/public-content-layout';
import PublicMarketingLayout from '@/layouts/public/public-marketing-layout';
import SettingsLayout from '@/layouts/settings/layout';

if (import.meta.env.VITE_REVERB_ENABLED === 'true') {
    configureEcho({ broadcaster: 'reverb' });
}

const appName = import.meta.env.VITE_PUBLIC_SITE_NAME || 'The Archive';
createInertiaApp({
    title: (title) => (title ? `${title} — ${appName}` : appName),
    layout: (name) => {
        if (name === 'welcome') {
            return PublicMarketingLayout;
        }

        if (name.startsWith('public/')) {
            return PublicContentLayout;
        }

        if (name.startsWith('auth/')) {
            return AuthLayout;
        }

        if (name.startsWith('onboarding/')) {
            return OnboardingLayout;
        }

        if (name.startsWith('settings/')) {
            return [AppLayout, SettingsLayout];
        }

        return AppLayout;
    },
    strictMode: true,
    withApp(app) {
        return (
            <ExperienceProvider>
                <TooltipProvider delayDuration={250}>
                    {app}
                    <Toaster />
                </TooltipProvider>
            </ExperienceProvider>
        );
    },
    progress: { color: '#78909c', showSpinner: false },
});
initializeTheme();
