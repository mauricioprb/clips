<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import BrandLogo from '@/Components/UI/BrandLogo.vue';
import StatusToast from '@/Components/UI/StatusToast.vue';

defineProps<{
    title: string;
    subtitle?: string;
}>();
</script>

<template>
    <Head :title="title" />
    <main class="flex min-h-dvh flex-col items-center justify-center px-6 py-10 sm:py-16">
        <div class="auth-form w-full max-w-md">
            <header class="text-center">
                <BrandLogo class="mx-auto mb-8 h-9" />
                <h1 class="text-3xl font-semibold tracking-tight">{{ title }}</h1>
                <p v-if="subtitle" class="mx-auto mt-3 max-w-sm text-sm leading-relaxed text-(--muted)">
                    {{ subtitle }}
                </p>
            </header>
            <div class="mt-8">
                <slot />
            </div>
        </div>
    </main>
    <StatusToast />
</template>

<style scoped>
.auth-form :deep(.p-inputtext) {
    min-height: 2.75rem;
    font-size: 1rem;
    background: var(--surface-raised);
}

.auth-form :deep(button[type='submit']) {
    min-height: 2.75rem;
}

.auth-form :deep(.p-password-mask-icon),
.auth-form :deep(.p-password-unmask-icon) {
    width: 2.75rem;
    height: 2.75rem;
    padding: 0.75rem;
    margin-top: -1.375rem;
    inset-inline-end: 0;
}
</style>
