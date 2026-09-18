<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Info } from '@lucide/vue';
import Button from 'primevue/button';
import { useI18n } from '@/Composables/useI18n';

defineProps<{
    setup: { profile: boolean; schedule: boolean; activities: boolean };
}>();

const { t } = useI18n();
</script>

<template>
    <div
        class="flex flex-wrap items-center gap-x-4 gap-y-3 rounded-(--radius-md) border border-(--accent)/30 bg-(--accent-soft) px-4 py-3 text-(--accent-soft-ink)"
    >
        <Info class="size-4 shrink-0" aria-hidden="true" />
        <p class="min-w-0 flex-1 basis-48 text-sm leading-relaxed">
            {{ setup.profile ? t('setup.activitiesHint') : t('setup.profileHint') }}
        </p>
        <Button
            :as="Link"
            :href="setup.profile ? '/atividades-padrao' : '/configuracoes/perfil'"
            variant="outlined"
            :label="setup.profile ? t('setup.activitiesAction') : t('setup.profileAction')"
        />
    </div>
</template>
