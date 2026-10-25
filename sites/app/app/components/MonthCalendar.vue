<script setup lang="ts">
import { Check, Plus } from '@lucide/vue';
import { parseDate, type DateValue } from '@internationalized/date';
import type { EntrySource, MonthDay } from '~/composables/useMonth';
import { Badge } from '~/ui/badge';
import { EventCalendarMonthView, type CalendarEvent, type EventColor } from '~/ui/event-calendar';

const props = defineProps<{ days: MonthDay[]; dailyTargetMinutes: number; today: string }>();
const emit = defineEmits<{ select: [date: string] }>();

const sourceLabels: Record<EntrySource, string> = {
  manual: 'Lançado por você',
  schedule: 'Grade semanal',
  activity: 'Preenchimento automático',
};
const sourceChipColors: Record<EntrySource, EventColor> = {
  manual: 'primary',
  schedule: 'neutral',
  activity: 'amber',
};
const sourceColors: Record<EntrySource, string> = {
  manual: 'bg-primary',
  schedule: 'bg-muted-foreground',
  activity: 'bg-golden-stone-500',
};

const anchor = computed(() => parseDate(props.days[0]?.date ?? toIsoDate(new Date())));
const todayValue = computed(() => parseDate(props.today));
const daysByDate = computed(() => new Map(props.days.map((day) => [day.date, day])));
const events = computed<CalendarEvent[]>(() =>
  props.days.flatMap((day) => {
    const [year = 1970, month = 1, date = 1] = day.date.split('-').map(Number);

    return day.entries.map((entry) => ({
      id: entry.id,
      title: entry.description,
      start: new Date(year, month - 1, date, 0, entry.startMinute),
      end: new Date(year, month - 1, date, 0, entry.endMinute - 1),
      color: sourceChipColors[entry.source],
    }));
  }),
);

function dayAt(value: DateValue): MonthDay {
  return daysByDate.value.get(value.toString()) as MonthDay;
}
const sources = computed(() =>
  (['manual', 'schedule', 'activity'] as const).filter((source) =>
    props.days.some((day) => day.entries.some((entry) => entry.source === source)),
  ),
);

function isComplete(day: MonthDay): boolean {
  return day.isWorkday && props.dailyTargetMinutes > 0 && day.minutes >= props.dailyTargetMinutes;
}

function hoursClass(day: MonthDay): string {
  if (isComplete(day)) return 'text-success';

  return day.isWorkday && day.minutes > 0 ? 'text-foreground' : 'text-muted-foreground';
}

function dayClass(day: MonthDay): string {
  if (day.date === props.today) return 'bg-primary-soft';

  return day.isWorkday ? 'bg-card' : 'bg-muted';
}

function label(day: MonthDay): string {
  return [
    `Abrir ${formatDayTitle(day.date)}`,
    day.holiday,
    day.isWorkday ? null : 'não entra no relatório',
    formatDuration(day.minutes),
    isComplete(day) ? 'dia completo' : null,
    ...day.entries.map((entry) => `${entry.description}, ${sourceLabels[entry.source]}`),
  ]
    .filter(Boolean)
    .join(', ');
}
</script>

<template>
  <div class="flex flex-col gap-3">
    <EventCalendarMonthView
      :anchor="anchor"
      :events="events"
      :today="todayValue"
      locale="pt-BR"
      first-day-of-week="mon"
      :max-visible="2"
      more-label="a mais"
      :day-label="(value) => label(dayAt(value))"
      :day-class="(value) => dayClass(dayAt(value))"
      class="hidden rounded-xl border border-border md:flex"
      @day-click="(value) => emit('select', value.toString())"
    >
      <template #day-badge="{ day: value }">
        <span
          v-if="dayAt(value).minutes > 0"
          class="flex items-center gap-0.5 text-caption font-semibold tabular-nums"
          :class="hoursClass(dayAt(value))"
        >
          <Check v-if="isComplete(dayAt(value))" class="size-3" aria-hidden="true" />
          {{ formatDuration(dayAt(value).minutes) }}
        </span>
      </template>
      <template #day-content="{ day: value }">
        <Badge
          v-if="dayAt(value).holiday"
          intent="violet"
          size="sm"
          class="mx-1 max-w-full self-start"
        >
          <span class="truncate">{{ dayAt(value).holiday }}</span>
        </Badge>
        <span v-else-if="!dayAt(value).isWorkday" class="px-1 text-caption text-muted-foreground">
          Fim de semana
        </span>
      </template>
      <template #day-footer="{ day: value }">
        <span
          v-if="!dayAt(value).entries.length && dayAt(value).isWorkday"
          class="mt-auto flex items-center gap-1 px-1 text-caption text-primary-text transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100"
          :class="value.toString() === today ? 'opacity-100' : 'opacity-0'"
          aria-hidden="true"
        >
          <Plus class="size-3" /> Registrar
        </span>
      </template>
    </EventCalendarMonthView>

    <ol class="divide-y divide-border overflow-hidden rounded-xl border border-border md:hidden">
      <li v-for="day in days" :key="day.date">
        <button
          type="button"
          class="flex min-h-14 w-full cursor-pointer items-center gap-3 px-4 py-3 text-left transition-colors hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none focus-visible:ring-inset"
          :class="dayClass(day)"
          :aria-label="label(day)"
          :aria-current="day.date === today ? 'date' : undefined"
          @click="emit('select', day.date)"
        >
          <span
            class="flex w-10 shrink-0 flex-col items-center"
            :class="
              day.date === today
                ? 'text-primary-text'
                : day.isWorkday
                  ? 'text-foreground'
                  : 'text-muted-foreground'
            "
          >
            <span class="text-caption font-medium">{{ formatShortWeekday(day.date) }}</span>
            <span class="text-heading-5 font-bold tabular-nums">{{ dayOfMonth(day.date) }}</span>
          </span>
          <span class="flex min-w-0 flex-1 flex-col gap-1">
            <span
              v-if="!day.entries.length && !day.holiday"
              class="text-body-sm text-muted-foreground"
            >
              {{ day.isWorkday ? 'Sem registros' : 'Fim de semana' }}
            </span>
            <Badge v-if="day.holiday" intent="violet" size="sm" class="max-w-full self-start">
              <span class="truncate">{{ day.holiday }}</span>
            </Badge>
            <span
              v-for="entry in day.entries.slice(0, 2)"
              :key="entry.id"
              class="flex min-w-0 items-baseline gap-2 text-body-sm"
            >
              <span
                class="size-1.5 shrink-0 self-center rounded-full"
                :class="sourceColors[entry.source]"
                aria-hidden="true"
              />
              <span class="shrink-0 text-caption text-muted-foreground tabular-nums">
                {{ minutesToClock(entry.startMinute) }}
              </span>
              <span class="truncate">{{ entry.description }}</span>
            </span>
            <span v-if="day.entries.length > 2" class="text-caption text-muted-foreground">
              mais {{ day.entries.length - 2 }}
            </span>
          </span>
          <span
            class="flex shrink-0 items-center gap-1 text-body-sm font-semibold tabular-nums"
            :class="hoursClass(day)"
          >
            <Check v-if="isComplete(day)" class="size-3.5" aria-hidden="true" />
            {{ day.minutes ? formatDuration(day.minutes) : '' }}
          </span>
          <Plus
            v-if="!day.entries.length"
            class="size-4 shrink-0 text-muted-foreground"
            aria-hidden="true"
          />
        </button>
      </li>
    </ol>

    <ul class="flex flex-wrap gap-x-5 gap-y-2 text-caption text-muted-foreground">
      <li v-for="source in sources" :key="source" class="flex items-center gap-1.5">
        <span
          class="size-1.5 shrink-0 rounded-full"
          :class="sourceColors[source]"
          aria-hidden="true"
        />
        {{ sourceLabels[source] }}
      </li>
      <li class="flex items-center gap-1.5">
        <span class="size-3 shrink-0 rounded-sm bg-muted ring-1 ring-border" aria-hidden="true" />
        Fim de semana e feriado não entram no relatório
      </li>
    </ul>
  </div>
</template>
