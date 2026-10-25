<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import type { DateValue, DayOfWeek } from '@internationalized/date';
import { cn } from '@/lib/utils';
import EventChip from './EventChip.vue';
import type { CalendarEvent } from './types';
import { getEventsForDay, getMonthMatrix, formatWeekday, toJsDate } from './utils';
import { useFuseLocale } from '@/lib/locale';

interface Props {
  anchor: DateValue;
  events?: CalendarEvent[];
  today?: DateValue;
  locale?: string;
  firstDayOfWeek?: DayOfWeek;
  maxVisible?: number;
  moreLabel?: string;
  dayLabel?: (day: DateValue) => string;
  dayClass?: (day: DateValue) => HTMLAttributes['class'];
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  events: () => [],
  locale: () => useFuseLocale().dateLocale,
  maxVisible: 3,
  moreLabel: () => useFuseLocale().eventCalendarMonthView.moreLabel,
});

const emit = defineEmits<{
  dayClick: [day: DateValue];
}>();

const weeks = computed(() => getMonthMatrix(props.anchor, props.locale, props.firstDayOfWeek));
const weekdays = computed(() =>
  (weeks.value[0] ?? []).map((date) => formatWeekday(date, props.locale, 'short').replace('.', '')),
);

const dayEvents = (day: DateValue) => getEventsForDay(props.events, day);

const isCurrentMonth = (day: DateValue) =>
  day.month === props.anchor.month && day.year === props.anchor.year;

const isToday = (day: DateValue) =>
  !!props.today &&
  day.day === props.today.day &&
  day.month === props.today.month &&
  day.year === props.today.year;
</script>

<template>
  <div :class="cn('flex flex-col overflow-hidden', props.class)">
    <div class="grid grid-cols-7 border-b border-border">
      <div
        v-for="(weekday, i) in weekdays"
        :key="i"
        class="px-3 py-2 text-caption font-medium text-muted-foreground capitalize"
      >
        {{ weekday }}
      </div>
    </div>

    <div class="-mb-px grid grid-cols-7">
      <template v-for="day in weeks.flat()" :key="toJsDate(day).getTime()">
        <button
          v-if="isCurrentMonth(day)"
          type="button"
          :aria-label="dayLabel?.(day)"
          :class="
            cn(
              'group flex min-h-28 min-w-0 cursor-pointer flex-col gap-1.5 border-r border-b border-border p-1.5 text-left transition-colors hover:bg-accent focus-visible:z-10 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none [&:nth-child(7n)]:border-r-0',
              dayClass?.(day),
            )
          "
          @click="emit('dayClick', day)"
        >
          <span class="flex items-center justify-between gap-2 px-1">
            <span
              class="inline-flex size-6 items-center justify-center rounded-full text-caption font-medium tabular-nums"
              :class="isToday(day) ? 'bg-primary text-primary-foreground' : 'text-foreground'"
            >
              {{ day.day }}
            </span>
            <slot name="day-badge" :day="day" />
          </span>

          <slot name="day-content" :day="day" />

          <span class="flex min-w-0 flex-col gap-1">
            <EventChip
              v-for="event in dayEvents(day).slice(0, maxVisible)"
              :key="event.id"
              as="span"
              :event="event"
              :locale="locale"
            />
            <span
              v-if="dayEvents(day).length > maxVisible"
              class="px-1.5 text-caption text-muted-foreground"
            >
              {{ dayEvents(day).length - maxVisible }} {{ moreLabel }}...
            </span>
          </span>

          <slot name="day-footer" :day="day" />
        </button>
        <span
          v-else
          aria-hidden="true"
          class="border-r border-b border-border bg-muted/30 [&:nth-child(7n)]:border-r-0"
        />
      </template>
    </div>
  </div>
</template>
