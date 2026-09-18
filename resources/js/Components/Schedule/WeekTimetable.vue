<script setup lang="ts">
import { computed } from 'vue';
import { Plus } from '@lucide/vue';
import Button from 'primevue/button';
import { useI18n } from '@/Composables/useI18n';
import { blockPosition, HOUR_HEIGHT, minuteAtOffset, visibleHours } from '@/Lib/timetable';
import { minutesToClock } from '@/Lib/time';
import type { WeeklySlot } from '@/types/models';

const props = defineProps<{
    slots: WeeklySlot[];
    weekdays: { value: number; label: string }[];
}>();

const emit = defineEmits<{
    create: [weekday: number, startMinute?: number];
    edit: [slot: WeeklySlot];
}>();

const { t } = useI18n();

const range = computed(() => visibleHours(props.slots));
const hours = computed(() =>
    Array.from({ length: range.value.endHour - range.value.startHour }, (_, index) => range.value.startHour + index),
);
const height = computed(() => `${hours.value.length * HOUR_HEIGHT}px`);

function slotsOf(weekday: number): WeeklySlot[] {
    return props.slots.filter((slot) => slot.weekday === weekday);
}

function style(slot: WeeklySlot): Record<string, string> {
    const { top, height: blockHeight } = blockPosition(slot, range.value.startHour);

    return { top: `${top}px`, height: `${Math.max(blockHeight, 22)}px` };
}

function createAt(weekday: number, event: MouseEvent): void {
    const column = event.currentTarget as HTMLElement;
    const offsetY = event.clientY - column.getBoundingClientRect().top;

    emit('create', weekday, minuteAtOffset(offsetY, range.value.startHour));
}
</script>

<template>
    <div class="card overflow-hidden">
        <div class="grid grid-cols-[3.5rem_repeat(5,minmax(0,1fr))] border-b border-(--line)">
            <span aria-hidden="true" />
            <div
                v-for="weekday in weekdays"
                :key="weekday.value"
                class="flex items-center justify-between border-l border-(--line) py-1.5 pr-1 pl-3"
            >
                <h2 class="text-sm font-semibold">{{ weekday.label }}</h2>
                <Button
                    icon-only
                    variant="text"
                    severity="secondary"
                    :aria-label="t('schedule.addOn', { weekday: weekday.label })"
                    @click="emit('create', weekday.value)"
                >
                    <Plus class="size-4" aria-hidden="true" />
                </Button>
            </div>
        </div>

        <div class="grid grid-cols-[3.5rem_repeat(5,minmax(0,1fr))]" :style="{ height }">
            <div class="relative" aria-hidden="true">
                <span
                    v-for="(hour, index) in hours"
                    :key="hour"
                    class="absolute right-2 -translate-y-1/2 text-[0.6875rem] text-(--muted) tabular-nums"
                    :style="{ top: `${index * HOUR_HEIGHT}px` }"
                    :class="index === 0 ? 'translate-y-0.5' : ''"
                >
                    {{ minutesToClock(hour * 60) }}
                </span>
            </div>

            <div
                v-for="weekday in weekdays"
                :key="weekday.value"
                class="relative cursor-pointer border-l border-(--line) transition-colors hover:bg-(--accent-soft)/40"
                :title="t('schedule.clickToAdd')"
                @click="createAt(weekday.value, $event)"
            >
                <span
                    v-for="(hour, index) in hours.slice(1)"
                    :key="hour"
                    class="pointer-events-none absolute inset-x-0 border-t border-(--line)"
                    :style="{ top: `${(index + 1) * HOUR_HEIGHT}px` }"
                    aria-hidden="true"
                />
                <button
                    v-for="slot in slotsOf(weekday.value)"
                    :key="slot.id"
                    type="button"
                    class="absolute inset-x-1 flex cursor-pointer flex-col overflow-hidden rounded-sm border-l-2 border-(--accent) bg-(--accent-soft) px-2 py-1 text-left text-(--accent-soft-ink) transition-colors hover:brightness-95 focus-visible:z-10"
                    :style="style(slot)"
                    :aria-label="t('schedule.editLabel', { description: slot.description, weekday: weekday.label })"
                    @click.stop="emit('edit', slot)"
                >
                    <span class="text-[0.6875rem] font-semibold tabular-nums">
                        {{ minutesToClock(slot.startMinute) }}-{{ minutesToClock(slot.endMinute) }}
                    </span>
                    <span class="text-xs leading-tight font-medium">{{ slot.description }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
