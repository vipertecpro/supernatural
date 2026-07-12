/// <reference types="vite/client" />

interface ImportMetaEnv {
    readonly VITE_APP_NAME?: string;
    readonly VITE_PUBLIC_SITE_NAME?: string;
    readonly VITE_REVERB_ENABLED?: string;
}

interface ImportMeta {
    readonly env: ImportMetaEnv;
}
