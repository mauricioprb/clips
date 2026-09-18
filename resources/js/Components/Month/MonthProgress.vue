<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from '@/Composables/useI18n';
import { formatDuration } from '@/Lib/time';

const props = defineProps<{
    workdayMinutes: number;
    targetMinutes: number;
    loggedMinutes: number;
    workdays: number;
    dailyTargetMinutes: number;
}>();

const { t } = useI18n();

const progress = computed(() =>
    props.targetMinutes > 0 ? Math.min(props.workdayMinutes / props.targetMinutes, 1) : 0,
);
const outsideMinutes = computed(() => Math.max(props.loggedMinutes - props.workdayMinutes, 0));
</script>

<template>
    <div class="grid gap-2 text-sm text-(--muted)">
        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
            <p>
                <strong class="font-semibold text-(--ink) tabular-nums">{{ formatDuration(workdayMinutes) }}</strong>
                {{ t('month.progress.of', { target: formatDuration(targetMinutes) }) }}
            </p>
            <div
                class="h-1 w-24 overflow-hidden rounded-full bg-(--surface-sunken)"
                role="progressbar"
                :aria-valuenow="Math.round(progress * 100)"
                aria-valuemin="0"
                aria-valuemax="100"
                :aria-label="t('month.progress.label')"
            >
                <div
                    class="h-full rounded-full transition-[width] duration-300"
                    :class="progress >= 1 ? 'bg-(--success)' : 'bg-(--accent)'"
                    :style="{ width: `${progress * 100}%` }"
                />
            </div>
            <p class="text-xs sm:ml-auto">
                {{ t('month.progress.basis', { days: workdays, daily: formatDuration(dailyTargetMinutes) }) }}
            </p>
        </div>
        <p v-if="outsideMinutes > 0" class="text-xs leading-relaxed">
            {{ t('month.progress.outside', { hours: formatDuration(outsideMinutes) }) }}
        </p>
    </div>
</template>
