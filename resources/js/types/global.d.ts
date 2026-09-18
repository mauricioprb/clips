import '@inertiajs/core';
import type { AuthUser } from '@/types/models';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        errorValueType: string;
        sharedPageProps: {
            auth: { user: AuthUser | null };
            status: string | null;
        };
    }
}
