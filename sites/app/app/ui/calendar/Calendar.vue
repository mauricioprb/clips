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
import { ChevronLeft, ChevronRight } from '@lucide/vue';
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
  monthLabel?: string;
  yearLabel?: string;
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
  monthLabel: () => useFuseLocale().calendar.monthLabel,
  yearLabel: () => useFuseLocale().calendar.yearLabel,
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

const monthNames = computed(() => {
  const formatter = new Intl.DateTimeFormat(props.locale ?? 'en', {
    month: 'long',
    timeZone: 'UTC',
  });
  return Array.from({ length: 12 }, (_, index) => {
    const name = formatter.format(new Date(Date.UTC(2000, index, 1)));
    return name.charAt(0).toLocaleUpperCase(props.locale) + name.slice(1);
  });
});

function years(shown: number) {
  const current = today(getLocalTimeZone()).year;
  const first = Math.min(props.minValue?.year ?? current - 100, shown);
  const last = Math.max(props.maxValue?.year ?? current + 10, shown);
  return Array.from({ length: last - first + 1 }, (_, index) => last - index);
}

function show(date: DateValue, unit: 'month' | 'year', event: Event) {
  placeholder.value = date.set({
    [unit]: Number((event.target as HTMLSelectElement).value),
  });
}

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
      <CalendarPrev
        :aria-label="prevLabel"
        class="inline-flex size-8 shrink-0 cursor-pointer items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
      >
        <ChevronLeft class="size-4" />
      </CalendarPrev>
      <div class="flex flex-1 items-center justify-center gap-1">
        <select
          :aria-label="monthLabel"
          :value="date.month"
          :disabled="disabled"
          class="h-8 cursor-pointer rounded-md bg-transparent px-1.5 text-body-md font-semibold text-foreground transition-colors outline-none hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring/50"
          @change="show(date, 'month', $event)"
        >
          <option v-for="(name, index) in monthNames" :key="name" :value="index + 1">
            {{ name }}
          </option>
        </select>
        <select
          :aria-label="yearLabel"
          :value="date.year"
          :disabled="disabled"
          class="h-8 cursor-pointer rounded-md bg-transparent px-1.5 text-body-md font-semibold text-foreground tabular-nums transition-colors outline-none hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring/50"
          @change="show(date, 'year', $event)"
        >
          <option v-for="year in years(date.year)" :key="year" :value="year">
            {{ year }}
          </option>
        </select>
      </div>
      <CalendarNext
        :aria-label="nextLabel"
        class="inline-flex size-8 shrink-0 cursor-pointer items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
      >
        <ChevronRight class="size-4" />
      </CalendarNext>
    </CalendarHeader>

    <div v-if="showInput || todayButton" class="flex items-center gap-2">
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

    <div class="flex flex-col gap-4 sm:flex-row sm:gap-6">
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
