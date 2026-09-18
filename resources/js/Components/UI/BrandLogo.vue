<script setup lang="ts">
import { useI18n } from '@/Composables/useI18n';

const { t } = useI18n();
</script>

<template>
    <span class="inline-flex">
        <img src="/images/logo_clips_light.svg" :alt="t('app.name')" class="h-full w-auto dark:hidden" />
        <img src="/images/logo_clips.svg" :alt="t('app.name')" class="hidden h-full w-auto dark:block" />
    </span>
</template>
