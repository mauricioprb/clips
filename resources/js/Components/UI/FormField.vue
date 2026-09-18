<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from '@/Composables/useI18n';

const props = defineProps<{
    id: string;
    label: string;
    error?: string;
    hint?: string;
    optional?: boolean;
}>();

const { t } = useI18n();

const describedBy = computed(
    () =>
        [props.hint ? `${props.id}-hint` : null, props.error ? `${props.id}-error` : null].filter(Boolean).join(' ') ||
        undefined,
);
</script>

<template>
    <div class="grid content-start gap-1.5">
        <label :for="id" class="text-sm font-medium">
            {{ label }}
            <span v-if="optional" class="font-normal text-(--muted)">({{ t('common.optional') }})</span>
        </label>
        <slot :id="id" :invalid="Boolean(error)" :described-by="describedBy" />
        <p v-if="hint" :id="`${id}-hint`" class="text-xs text-(--muted)">{{ hint }}</p>
        <p v-if="error" :id="`${id}-error`" class="text-sm font-medium text-(--danger)">{{ error }}</p>
    </div>
</template>
