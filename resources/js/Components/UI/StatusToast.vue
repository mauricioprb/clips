<script setup lang="ts">
import { onBeforeUnmount, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { isTranslationKey, useI18n } from '@/Composables/useI18n';

const { t } = useI18n();
const toast = useToast();

function show(status: string | null): void {
    if (!status) {
        return;
    }

    const key = `status.${status}`;

    toast.add({ severity: 'success', summary: isTranslationKey(key) ? t(key) : status, life: 4000 });
}

const stopListening = router.on('success', (event) => show(event.detail.page.props.status));

onMounted(() => show(usePage().props.status));
onBeforeUnmount(stopListening);
</script>

<template>
    <Toast
        position="bottom-center"
        class="mb-16 max-w-[calc(100vw-2rem)] md:mb-0"
        :pt="{ messageIcon: { class: 'text-(--success)' } }"
    />
</template>
