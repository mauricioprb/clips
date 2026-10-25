<script setup lang="ts">
import { computed, ref, watch, type HTMLAttributes } from 'vue';
import {
  CalendarRoot,
  CalendarHeader,
  CalendarPrev,
  CalendarNext,
  CalendarGrid,
  CalendarGridHead,
  CalendarGridBody,
  CalendarGridRow,
  CalendarHeadCell,
  CalendarCell,
  CalendarCellTrigger,
  type DateValue,
} from 'reka-ui';
import { ChevronDown, ChevronLeft, ChevronRight } from '@lucide/vue';
import { CalendarDate, getLocalTimeZone, today } from '@internationalized/date';
import { cn } from '@/lib/utils';
import { calendarCellTriggerVariants } from '.';
import { useFuseLocale } from '@/lib/locale';

type Matcher = (date: DateValue) => boolean;

interface Props {
  weekStartsOn?: 0 | 1 | 2 | 3 | 4 | 5 | 6;
  weekdayFormat?: 'narrow' | 'short' | 'long';
  fixedWeeks?: boolean;
  numberOfMonths?: number;
  minValue?: DateValue;
  maxValue?: DateValue;
  locale?: string;
  disabled?: boolean;
  readonly?: boolean;
  pagedNavigation?: boolean;
  preventDeselect?: boolean;
  calendarLabel?: string;
  isDateDisabled?: Matcher;
  isDateUnavailable?: Matcher;
  isDateMarked?: Matcher;
  prevLabel?: string;
  nextLabel?: string;
  viewLabel?: string;
  prevYearLabel?: string;
  nextYearLabel?: string;
  prevYearsLabel?: string;
  nextYearsLabel?: string;
  showInput?: boolean;
  todayButton?: boolean;
  inputPlaceholder?: string;
  todayLabel?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  weekStartsOn: 1,
  weekdayFormat: 'short',
  fixedWeeks: true,
  prevLabel: () => useFuseLocale().calendar.prevLabel,
  nextLabel: () => useFuseLocale().calendar.nextLabel,
  viewLabel: () => useFuseLocale().calendar.viewLabel,
  prevYearLabel: () => useFuseLocale().calendar.prevYearLabel,
  nextYearLabel: () => useFuseLocale().calendar.nextYearLabel,
  prevYearsLabel: () => useFuseLocale().calendar.prevYearsLabel,
  nextYearsLabel: () => useFuseLocale().calendar.nextYearsLabel,
  showInput: false,
  todayButton: false,
  inputPlaceholder: () => useFuseLocale().calendar.inputPlaceholder,
  todayLabel: () => useFuseLocale().calendar.todayLabel,
});

const model = defineModel<DateValue>();
const placeholder = defineModel<DateValue>('placeholder');

const pad = (value: number) => String(value).padStart(2, '0');
const formatInput = (value?: DateValue) =>
  value ? `${pad(value.day)}/${pad(value.month)}/${value.year}` : '';

const dateText = ref(formatInput(model.value));

watch(model, (value) => {
  dateText.value = formatInput(value);
});

function commitInput() {
  const match = dateText.value.trim().match(/^(\d{1,2})\s*\/\s*(\d{1,2})\s*\/\s*(\d{4})$/);
  if (match) {
    const day = Number(match[1]);
    const month = Number(match[2]);
    const year = Number(match[3]);
    if (month >= 1 && month <= 12 && day >= 1 && day <= 31) {
      try {
        const date = new CalendarDate(year, month, day);
        if (date.day === day && date.month === month) {
          model.value = date;
          placeholder.value = date;
        }
      } catch {
        dateText.value = formatInput(model.value);
      }
    }
  }
  dateText.value = formatInput(model.value);
}

type View = 'days' | 'months' | 'years';

const view = ref<View>('days');
const pageStart = ref(0);
const thisMonth = today(getLocalTimeZone());

const monthNames = computed(() => {
  const formatter = new Intl.DateTimeFormat(props.locale ?? 'en', {
    month: 'short',
    timeZone: 'UTC',
  });
  return Array.from({ length: 12 }, (_, index) => {
    const name = formatter.format(new Date(Date.UTC(2000, index, 1))).replace(/\.$/, '');
    return name.charAt(0).toLocaleUpperCase(props.locale) + name.slice(1);
  });
});

const years = computed(() => Array.from({ length: 12 }, (_, index) => pageStart.value + index));

function heading(date: DateValue) {
  if (view.value === 'years') return `${pageStart.value} – ${pageStart.value + 11}`;
  if (view.value === 'months') return String(date.year);
  const text = new Intl.DateTimeFormat(props.locale ?? 'en', {
    month: 'long',
    year: 'numeric',
    timeZone: 'UTC',
  }).format(new Date(Date.UTC(date.year, date.month - 1, 1)));
  return text.charAt(0).toLocaleUpperCase(props.locale) + text.slice(1);
}

function toggleView(date: DateValue) {
  if (view.value === 'days') {
    view.value = 'months';
  } else if (view.value === 'months') {
    pageStart.value = date.year - (date.year % 12);
    view.value = 'years';
  } else {
    view.value = 'days';
  }
}

function step(date: DateValue, direction: 1 | -1) {
  if (view.value === 'years') pageStart.value += 12 * direction;
  else placeholder.value = date.add({ years: direction });
}

function pickMonth(date: DateValue, month: number) {
  placeholder.value = date.set({ month });
  view.value = 'days';
}

function pickYear(date: DateValue, year: number) {
  placeholder.value = date.set({ year });
  view.value = 'months';
}

const outOfRange = (year: number, month?: number) => {
  const key = (y: number, m: number) => y * 12 + m;
  const first = key(year, month ?? 1);
  const last = key(year, month ?? 12);
  return (
    (!!props.minValue && last < key(props.minValue.year, props.minValue.month)) ||
    (!!props.maxValue && first > key(props.maxValue.year, props.maxValue.month))
  );
};

const navClass =
  'inline-flex size-8 shrink-0 cursor-pointer items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50';

const pickerCellClass = cn(calendarCellTriggerVariants(), 'h-9 w-full');

function goToday() {
  const date = today(getLocalTimeZone());
  model.value = date;
  placeholder.value = date;
}
</script>

<template>
  <CalendarRoot
    v-slot="{ grid, weekDays, date }"
    v-model="model"
    v-model:placeholder="placeholder"
    :week-starts-on="weekStartsOn"
    :weekday-format="weekdayFormat"
    :fixed-weeks="fixedWeeks"
    :number-of-months="numberOfMonths"
    :min-value="minValue"
    :max-value="maxValue"
    :locale="locale"
    :disabled="disabled"
    :readonly="readonly"
    :paged-navigation="pagedNavigation"
    :prevent-deselect="preventDeselect"
    :calendar-label="calendarLabel"
    :is-date-disabled="isDateDisabled"
    :is-date-unavailable="isDateUnavailable"
    :class="cn('flex w-fit flex-col gap-4', props.class)"
  >
    <CalendarHeader class="flex items-center justify-between gap-2">
      <CalendarPrev v-if="view === 'days'" :aria-label="prevLabel" :class="navClass">
        <ChevronLeft class="size-4" />
      </CalendarPrev>
      <button
        v-else
        type="button"
        :aria-label="view === 'months' ? prevYearLabel : prevYearsLabel"
        :class="navClass"
        @click="step(date, -1)"
      >
        <ChevronLeft class="size-4" />
      </button>
      <button
        type="button"
        :aria-label="viewLabel"
        :aria-expanded="view !== 'days'"
        :disabled="disabled"
        class="inline-flex h-8 cursor-pointer items-center gap-1 rounded-md px-2 text-body-md font-semibold text-foreground tabular-nums transition-colors hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none"
        @click="toggleView(date)"
      >
        <span aria-live="polite">{{ heading(date) }}</span>
        <ChevronDown
          class="size-4 text-muted-foreground transition-transform"
          :class="view !== 'days' && 'rotate-180'"
        />
      </button>
      <CalendarNext v-if="view === 'days'" :aria-label="nextLabel" :class="navClass">
        <ChevronRight class="size-4" />
      </CalendarNext>
      <button
        v-else
        type="button"
        :aria-label="view === 'months' ? nextYearLabel : nextYearsLabel"
        :class="navClass"
        @click="step(date, 1)"
      >
        <ChevronRight class="size-4" />
      </button>
    </CalendarHeader>

    <div
      v-if="view === 'months'"
      class="grid h-64 w-63 grid-cols-3 grid-rows-4 items-center gap-x-2"
    >
      <button
        v-for="(name, index) in monthNames"
        :key="name"
        type="button"
        :class="pickerCellClass"
        :data-selected="date.month === index + 1 || undefined"
        :data-today="(thisMonth.year === date.year && thisMonth.month === index + 1) || undefined"
        :data-disabled="outOfRange(date.year, index + 1) || undefined"
        :aria-pressed="date.month === index + 1"
        @click="pickMonth(date, index + 1)"
      >
        {{ name }}
      </button>
    </div>

    <div
      v-else-if="view === 'years'"
      class="grid h-64 w-63 grid-cols-3 grid-rows-4 items-center gap-x-2"
    >
      <button
        v-for="year in years"
        :key="year"
        type="button"
        :class="pickerCellClass"
        :data-selected="date.year === year || undefined"
        :data-today="thisMonth.year === year || undefined"
        :data-disabled="outOfRange(year) || undefined"
        :aria-pressed="date.year === year"
        @click="pickYear(date, year)"
      >
        {{ year }}
      </button>
    </div>

    <div v-if="view === 'days' && (showInput || todayButton)" class="flex items-center gap-2">
      <input
        v-if="showInput"
        v-model="dateText"
        type="text"
        inputmode="numeric"
        :placeholder="inputPlaceholder"
        class="h-8 min-w-0 flex-1 rounded-md border border-border bg-transparent px-2.5 text-body-sm tabular-nums text-foreground outline-none transition-colors focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/30"
        @change="commitInput"
      />
      <button
        v-if="todayButton"
        type="button"
        class="h-8 shrink-0 cursor-pointer rounded-md border border-border px-3 text-action-md font-medium text-foreground transition-colors hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none"
        @click="goToday"
      >
        {{ todayLabel }}
      </button>
    </div>

    <div v-if="view === 'days'" class="flex flex-col gap-4 sm:flex-row sm:gap-6">
      <CalendarGrid
        v-for="month in grid"
        :key="month.value.toString()"
        class="w-full border-collapse select-none"
      >
        <CalendarGridHead>
          <CalendarGridRow class="flex">
            <CalendarHeadCell
              v-for="day in weekDays"
              :key="day"
              class="flex w-9 shrink-0 items-center justify-center pb-1 text-caption font-normal text-muted-foreground capitalize"
            >
              {{ day.replace(/\.$/, '') }}
            </CalendarHeadCell>
          </CalendarGridRow>
        </CalendarGridHead>
        <CalendarGridBody class="flex flex-col gap-1">
          <CalendarGridRow
            v-for="(weekDates, index) in month.rows"
            :key="`week-${index}`"
            class="flex"
          >
            <CalendarCell
              v-for="weekDate in weekDates"
              :key="weekDate.toString()"
              :date="weekDate"
              class="w-9 shrink-0 p-0 text-center"
            >
              <CalendarCellTrigger
                :day="weekDate"
                :month="month.value"
                :class="calendarCellTriggerVariants()"
              >
                <slot name="day" :date="weekDate">
                  {{ weekDate.day }}
                </slot>
                <span
                  v-if="isDateMarked?.(weekDate)"
                  class="absolute bottom-1 left-1/2 size-1 -translate-x-1/2 rounded-full bg-primary group-data-[selected]:bg-primary-foreground"
                />
              </CalendarCellTrigger>
            </CalendarCell>
          </CalendarGridRow>
        </CalendarGridBody>
      </CalendarGrid>
    </div>
  </CalendarRoot>
</template>
