<script setup lang="ts">
import { computed } from 'vue';
import { Check, Plus } from '@lucide/vue';
import Button from 'primevue/button';
import { useI18n, type TranslationKey } from '@/Composables/useI18n';
import { dayOfMonth, formatDayTitle, formatShortWeekday, isoWeekday } from '@/Lib/dates';
import { calendarWeeks } from '@/Lib/month';
import { formatDuration, minutesToClock } from '@/Lib/time';
import type { EntrySource, MonthDay } from '@/types/models';

const props = defineProps<{
    days: MonthDay[];
    dailyTargetMinutes: number;
    today: string;
}>();

const emit = defineEmits<{ select: [date: string] }>();

const { t } = useI18n();

const weeks = computed(() => calendarWeeks(props.days));
const weekdayLabels = [1, 2, 3, 4, 5, 6, 7].map((weekday) => t(`month.weekday.${weekday}` as TranslationKey));
const sourceColors: Record<EntrySource, string> = {
    manual: 'bg-(--source-manual)',
    schedule: 'bg-(--source-schedule)',
    activity: 'bg-(--source-activity)',
};
const sources = computed(() =>
    (['manual', 'schedule', 'activity'] as const).filter((source) =>
        props.days.some((day) => day.entries.some((entry) => entry.source === source)),
    ),
);

function isComplete(day: MonthDay): boolean {
    return day.isWorkday && props.dailyTargetMinutes > 0 && day.minutes >= props.dailyTargetMinutes;
}

function hoursClass(day: MonthDay): string {
    if (isComplete(day)) {
        return 'text-(--success)';
    }

    return day.isWorkday && day.minutes > 0 ? 'text-(--ink)' : 'text-(--muted)';
}

function label(day: MonthDay): string {
    return [
        t('month.day.open', { date: formatDayTitle(day.date) }),
        day.holiday,
        formatDuration(day.minutes),
        isComplete(day) ? t('month.legend.complete') : null,
        ...day.entries.map((entry) => `${entry.description}, ${t(`month.legend.${entry.source}` as TranslationKey)}`),
    ]
        .filter(Boolean)
        .join(', ');
}

function isWeekend(day: MonthDay): boolean {
    return isoWeekday(day.date) > 5;
}
</script>

<template>
    <div class="grid gap-3">
        <div class="card hidden overflow-hidden md:block">
            <div class="grid grid-cols-7 border-b border-(--line)">
                <span
                    v-for="(weekday, index) in weekdayLabels"
                    :key="weekday"
                    class="px-3 py-3 text-xs font-medium text-(--muted)"
                    :class="index > 4 ? 'bg-(--cell-muted)' : ''"
                    aria-hidden="true"
                >
                    {{ weekday }}
                </span>
            </div>
            <div
                v-for="(week, weekIndex) in weeks"
                :key="weekIndex"
                class="grid grid-cols-7 border-b border-(--line) last:border-b-0"
            >
                <template v-for="(day, dayIndex) in week" :key="day?.date ?? `blank-${weekIndex}-${dayIndex}`">
                    <Button
                        v-if="day"
                        unstyled
                        class="group relative flex min-h-28 min-w-0 cursor-pointer flex-col gap-2 border-r border-(--line) p-2.5 text-left transition-colors last:border-r-0 hover:bg-(--accent-soft) focus-visible:z-10 focus-visible:-outline-offset-2"
                        :class="
                            day.date === today
                                ? 'bg-(--calendar-today)'
                                : day.isWorkday
                                  ? 'bg-(--surface-raised)'
                                  : 'bg-(--calendar-rest)'
                        "
                        :aria-label="label(day)"
                        :aria-current="day.date === today ? 'date' : undefined"
                        @click="emit('select', day.date)"
                    >
                        <span class="flex items-center justify-between gap-2">
                            <span
                                class="grid size-7 place-items-center rounded-(--radius-md) text-xs font-semibold tabular-nums"
                                :class="
                                    day.date === today
                                        ? 'bg-(--accent) text-(--accent-ink)'
                                        : day.isWorkday
                                          ? 'text-(--ink)'
                                          : 'text-(--muted)'
                                "
                            >
                                {{ dayOfMonth(day.date) }}
                            </span>
                            <span
                                v-if="day.minutes > 0"
                                class="flex items-center gap-0.5 text-xs font-semibold tabular-nums"
                                :class="hoursClass(day)"
                            >
                                <Check v-if="isComplete(day)" class="size-3" aria-hidden="true" />
                                {{ formatDuration(day.minutes) }}
                            </span>
                        </span>
                        <span v-if="day.holiday" class="px-0.5 text-xs leading-snug text-(--muted)">
                            {{ day.holiday }}
                        </span>
                        <span class="grid min-w-0 gap-1.5">
                            <span
                                v-for="entry in day.entries.slice(0, 2)"
                                :key="entry.id"
                                class="grid min-w-0 gap-1 rounded-sm bg-(--surface-sunken) px-2 py-1.5 text-xs leading-4"
                                :title="`${minutesToClock(entry.startMinute)}-${minutesToClock(entry.endMinute)} ${entry.description}, ${t(`month.legend.${entry.source}` as TranslationKey)}`"
                            >
                                <span class="flex min-w-0 items-center gap-1.5">
                                    <span
                                        class="size-1.5 shrink-0 rounded-full"
                                        :class="sourceColors[entry.source]"
                                        aria-hidden="true"
                                    />
                                    <span class="truncate font-medium text-(--ink)">{{ entry.description }}</span>
                                </span>
                                <span class="text-(--muted) tabular-nums"
                                    >{{ minutesToClock(entry.startMinute) }} -
                                    {{ minutesToClock(entry.endMinute) }}</span
                                >
                            </span>
                            <span
                                v-if="day.entries.length > 2"
                                class="px-1.5 text-[0.6875rem] font-medium text-(--muted)"
                            >
                                {{ t('month.day.more', { count: day.entries.length - 2 }) }}
                            </span>
                        </span>
                        <span
                            v-if="!day.entries.length && day.isWorkday"
                            class="mt-auto flex items-center gap-1 text-xs text-(--accent-soft-ink) transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100"
                            :class="day.date === today ? 'opacity-100' : 'opacity-0'"
                            aria-hidden="true"
                        >
                            <Plus class="size-3" />
                            {{ t('month.day.add') }}
                        </span>
                    </Button>
                    <span
                        v-else
                        class="border-r border-(--line) bg-(--calendar-rest) last:border-r-0"
                        aria-hidden="true"
                    />
                </template>
            </div>
        </div>

        <ol class="card divide-y divide-(--line) overflow-hidden md:hidden">
            <li v-for="day in days" :key="day.date">
                <Button
                    unstyled
                    class="flex min-h-14 w-full cursor-pointer items-center gap-3 px-4 text-left transition-colors hover:bg-(--accent-soft) focus-visible:-outline-offset-2"
                    :class="[
                        isWeekend(day) && !day.minutes ? 'py-2' : 'py-3',
                        day.date === today ? 'bg-(--calendar-today)' : day.isWorkday ? '' : 'bg-(--calendar-rest)',
                    ]"
                    :aria-label="label(day)"
                    :aria-current="day.date === today ? 'date' : undefined"
                    @click="emit('select', day.date)"
                >
                    <span
                        class="grid w-10 shrink-0 justify-items-center leading-tight"
                        :class="day.date === today ? 'text-(--accent)' : day.isWorkday ? '' : 'text-(--muted)'"
                    >
                        <span class="text-[0.6875rem] font-medium">{{ formatShortWeekday(day.date) }}</span>
                        <span class="text-lg font-bold tabular-nums">{{ dayOfMonth(day.date) }}</span>
                    </span>
                    <span class="grid min-w-0 flex-1 gap-1">
                        <span v-if="!day.entries.length && !day.holiday" class="text-sm text-(--muted)">{{
                            day.isWorkday ? t('month.day.empty') : t('month.day.weekend')
                        }}</span>
                        <span v-if="day.holiday" class="truncate text-xs text-(--muted)">{{ day.holiday }}</span>
                        <span
                            v-for="entry in day.entries.slice(0, 2)"
                            :key="entry.id"
                            class="flex min-w-0 items-baseline gap-2 text-sm"
                            :title="t(`month.legend.${entry.source}` as TranslationKey)"
                        >
                            <span
                                class="size-1.5 shrink-0 self-center rounded-full"
                                :class="sourceColors[entry.source]"
                                aria-hidden="true"
                            />
                            <span class="shrink-0 text-xs text-(--muted) tabular-nums">{{
                                minutesToClock(entry.startMinute)
                            }}</span>
                            <span class="truncate">{{ entry.description }}</span>
                        </span>
                        <span v-if="day.entries.length > 2" class="text-xs text-(--muted)">
                            {{ t('month.day.more', { count: day.entries.length - 2 }) }}
                        </span>
                    </span>
                    <span
                        class="flex shrink-0 items-center gap-1 text-sm font-semibold tabular-nums"
                        :class="hoursClass(day)"
                    >
                        <Check v-if="isComplete(day)" class="size-3.5" aria-hidden="true" />
                        {{ day.minutes ? formatDuration(day.minutes) : '' }}
                    </span>
                    <Plus v-if="!day.entries.length" class="size-4 shrink-0 text-(--muted)" aria-hidden="true" />
                </Button>
            </li>
        </ol>
        <ul v-if="sources.length" class="flex flex-wrap gap-x-5 gap-y-2 text-xs text-(--muted)">
            <li v-for="source in sources" :key="source" class="flex items-center gap-1.5">
                <span class="size-1.5 shrink-0 rounded-full" :class="sourceColors[source]" aria-hidden="true" />
                {{ t(`month.legend.${source}` as TranslationKey) }}
            </li>
        </ul>
    </div>
</template>
