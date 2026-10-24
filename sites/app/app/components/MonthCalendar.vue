<script setup lang="ts">
import { Check, Plus } from '@lucide/vue';
import type { EntrySource, MonthDay } from '~/composables/useMonth';

const props = defineProps<{ days: MonthDay[]; dailyTargetMinutes: number; today: string }>();
const emit = defineEmits<{ select: [date: string] }>();

const weekdayLabels = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
const sourceLabels: Record<EntrySource, string> = {
  manual: 'Lançado por você',
  schedule: 'Grade semanal',
  activity: 'Preenchimento automático',
};
const sourceColors: Record<EntrySource, string> = {
  manual: 'bg-primary',
  schedule: 'bg-muted-foreground',
  activity: 'bg-warning',
};

const weeks = computed(() => calendarWeeks(props.days));
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
    <div class="hidden overflow-hidden rounded-xl border border-border md:block">
      <div class="grid grid-cols-7 border-b border-border">
        <span
          v-for="(weekday, index) in weekdayLabels"
          :key="weekday"
          class="px-3 py-3 text-caption font-medium text-muted-foreground"
          :class="index > 4 ? 'bg-muted' : 'bg-card'"
          aria-hidden="true"
        >
          {{ weekday }}
        </span>
      </div>
      <div
        v-for="(week, weekIndex) in weeks"
        :key="weekIndex"
        class="grid grid-cols-7 border-b border-border last:border-b-0"
      >
        <template
          v-for="(day, dayIndex) in week"
          :key="day?.date ?? `blank-${weekIndex}-${dayIndex}`"
        >
          <button
            v-if="day"
            type="button"
            class="group relative flex min-h-28 min-w-0 cursor-pointer flex-col gap-2 border-r border-border p-2.5 text-left transition-colors last:border-r-0 hover:bg-accent focus-visible:z-10 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
            :class="dayClass(day)"
            :aria-label="label(day)"
            :aria-current="day.date === today ? 'date' : undefined"
            @click="emit('select', day.date)"
          >
            <span class="flex items-center justify-between gap-2">
              <span
                class="grid size-7 place-items-center rounded-md text-caption font-semibold tabular-nums"
                :class="
                  day.date === today
                    ? 'bg-primary text-primary-foreground'
                    : day.isWorkday
                      ? 'text-foreground'
                      : 'text-muted-foreground'
                "
              >
                {{ dayOfMonth(day.date) }}
              </span>
              <span
                v-if="day.minutes > 0"
                class="flex items-center gap-0.5 text-caption font-semibold tabular-nums"
                :class="hoursClass(day)"
              >
                <Check v-if="isComplete(day)" class="size-3" aria-hidden="true" />
                {{ formatDuration(day.minutes) }}
              </span>
            </span>
            <span v-if="day.holiday" class="px-0.5 text-caption text-muted-foreground">
              {{ day.holiday }}
            </span>
            <span class="flex min-w-0 flex-col gap-1.5">
              <span
                v-for="entry in day.entries.slice(0, 2)"
                :key="entry.id"
                class="flex min-w-0 flex-col gap-1 rounded-sm bg-muted px-2 py-1.5 text-caption"
              >
                <span class="flex min-w-0 items-center gap-1.5">
                  <span
                    class="size-1.5 shrink-0 rounded-full"
                    :class="sourceColors[entry.source]"
                    aria-hidden="true"
                  />
                  <span class="truncate font-medium text-foreground">{{ entry.description }}</span>
                </span>
                <span class="text-muted-foreground tabular-nums">
                  {{ minutesToClock(entry.startMinute) }} - {{ minutesToClock(entry.endMinute) }}
                </span>
              </span>
              <span
                v-if="day.entries.length > 2"
                class="px-1.5 text-caption font-medium text-muted-foreground"
              >
                mais {{ day.entries.length - 2 }}
              </span>
            </span>
            <span
              v-if="!day.entries.length && day.isWorkday"
              class="mt-auto flex items-center gap-1 text-caption text-primary-text transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100"
              :class="day.date === today ? 'opacity-100' : 'opacity-0'"
              aria-hidden="true"
            >
              <Plus class="size-3" /> Registrar
            </span>
          </button>
          <span v-else class="border-r border-border bg-muted last:border-r-0" aria-hidden="true" />
        </template>
      </div>
    </div>

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
            <span v-if="day.holiday" class="truncate text-caption text-muted-foreground">
              {{ day.holiday }}
            </span>
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

    <ul
      v-if="sources.length"
      class="flex flex-wrap gap-x-5 gap-y-2 text-caption text-muted-foreground"
    >
      <li v-for="source in sources" :key="source" class="flex items-center gap-1.5">
        <span
          class="size-1.5 shrink-0 rounded-full"
          :class="sourceColors[source]"
          aria-hidden="true"
        />
        {{ sourceLabels[source] }}
      </li>
    </ul>
  </div>
</template>
