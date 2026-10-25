<script setup lang="ts">
import { computed, ref, shallowRef, useId, watch, type HTMLAttributes } from 'vue';
import { Calendar as CalendarIcon, CircleX } from '@lucide/vue';
import {
  CalendarDate,
  CalendarDateTime,
  DateFormatter,
  getLocalTimeZone,
  today,
} from '@internationalized/date';
import type { DateValue } from 'reka-ui';
import { cn } from '@/lib/utils';
import { Label } from '@/ui/label';
import { FieldMessage } from '@/ui/field-message';
import { inputWrapperVariants, type InputWrapperVariants } from '@/ui/input';
import { Popover, PopoverAnchor, PopoverTrigger, PopoverContent } from '@/ui/popover';
import { Button } from '@/ui/button';
import { ScrollArea } from '@/ui/scroll-area';
import { Calendar } from '@/ui/calendar';
import { useFuseLocale } from '@/lib/locale';

type Matcher = (date: DateValue) => boolean;

interface Props {
  label?: string;
  hint?: string;
  error?: string;
  placeholder?: string;
  required?: boolean;
  status?: InputWrapperVariants['status'];
  disabled?: boolean;
  clearable?: boolean;
  clearLabel?: string;
  locale?: string;
  dateStyle?: 'full' | 'long' | 'medium' | 'short';
  weekStartsOn?: 0 | 1 | 2 | 3 | 4 | 5 | 6;
  minValue?: DateValue;
  maxValue?: DateValue;
  isDateDisabled?: Matcher;
  isDateUnavailable?: Matcher;
  showTime?: boolean;
  timeSlots?: string[];
  minTime?: string;
  maxTime?: string;
  timeStep?: number;
  todayLabel?: string;
  applyLabel?: string;
  cancelLabel?: string;
  id?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  status: 'default',
  placeholder: () => useFuseLocale().datePicker.placeholder,
  clearLabel: () => useFuseLocale().datePicker.clearLabel,
  locale: () => useFuseLocale().dateLocale,
  dateStyle: 'long',
  showTime: false,
  minTime: '09:00',
  maxTime: '17:00',
  timeStep: 30,
  todayLabel: () => useFuseLocale().datePicker.todayLabel,
  applyLabel: () => useFuseLocale().datePicker.applyLabel,
  cancelLabel: () => useFuseLocale().datePicker.cancelLabel,
});

const model = defineModel<DateValue>();

defineOptions({ inheritAttrs: false });

const open = ref(false);

const autoId = useId();
const fieldId = computed(() => props.id ?? autoId);
const messageId = computed(() => `${fieldId.value}-message`);
const resolvedStatus = computed(() => (props.error ? 'error' : props.status));
const message = computed(() => props.error ?? props.hint);

const hasTime = (value?: DateValue): value is CalendarDateTime => !!value && 'hour' in value;

const formatted = computed(() => {
  if (!model.value) return '';
  const options = hasTime(model.value)
    ? { dateStyle: props.dateStyle, timeStyle: 'short' as const }
    : { dateStyle: props.dateStyle };
  return new DateFormatter(props.locale, options).format(model.value.toDate(getLocalTimeZone()));
});

const draftDate = shallowRef<DateValue | undefined>();
const draftTime = ref<number | undefined>();
const dateText = ref('');
const timeText = ref('');

const calendarModel = computed({
  get: () => (props.showTime ? draftDate.value : model.value),
  set: (value) => {
    if (props.showTime) draftDate.value = value;
    else model.value = value;
  },
});

const toMinutes = (value: string) => {
  const [hour, minute] = value.split(':').map(Number);
  return (hour ?? 0) * 60 + (minute || 0);
};

const slotMinutes = computed(() => {
  if (props.timeSlots) return props.timeSlots.map(toMinutes);
  const start = toMinutes(props.minTime);
  const end = toMinutes(props.maxTime);
  const slots: number[] = [];
  for (let minute = start; minute <= end; minute += props.timeStep) {
    slots.push(minute);
  }
  return slots;
});

const timeFormatter = computed(
  () => new DateFormatter(props.locale, { hour: 'numeric', minute: '2-digit' }),
);

const formatSlot = (minutes: number) =>
  timeFormatter.value.format(new Date(2000, 0, 1, Math.floor(minutes / 60), minutes % 60));

const pad = (value: number) => String(value).padStart(2, '0');

const minutesToHHMM = (minutes: number) => `${pad(Math.floor(minutes / 60))}:${pad(minutes % 60)}`;

function parseHHMM(text: string): number | undefined {
  const match = text.trim().match(/^(\d{1,2}):(\d{2})$/);
  if (!match) return undefined;
  const hour = Number(match[1]);
  const minute = Number(match[2]);
  if (hour > 23 || minute > 59) return undefined;
  return hour * 60 + minute;
}

const formatInput = (value?: DateValue) =>
  value ? `${pad(value.day)}/${pad(value.month)}/${value.year}` : '';

function parseInput(text: string): DateValue | undefined {
  const match = text.trim().match(/^(\d{1,2})\s*\/\s*(\d{1,2})\s*\/\s*(\d{4})$/);
  if (!match) return undefined;
  const day = Number(match[1]);
  const month = Number(match[2]);
  const year = Number(match[3]);
  if (month < 1 || month > 12 || day < 1 || day > 31) return undefined;
  try {
    const date = new CalendarDate(year, month, day);
    return date.day === day && date.month === month ? date : undefined;
  } catch {
    return undefined;
  }
}

watch(draftDate, () => {
  dateText.value = formatInput(draftDate.value);
});

watch(draftTime, () => {
  timeText.value = draftTime.value != null ? minutesToHHMM(draftTime.value) : '';
});

watch(open, (isOpen) => {
  if (!isOpen || !props.showTime) return;
  draftDate.value = model.value
    ? new CalendarDate(model.value.year, model.value.month, model.value.day)
    : undefined;
  draftTime.value = hasTime(model.value) ? model.value.hour * 60 + model.value.minute : undefined;
  dateText.value = formatInput(draftDate.value);
  timeText.value = draftTime.value != null ? minutesToHHMM(draftTime.value) : '';
});

watch(model, () => {
  if (!props.showTime) open.value = false;
});

function commitDateInput() {
  const parsed = parseInput(dateText.value);
  if (parsed) draftDate.value = parsed;
  dateText.value = formatInput(draftDate.value);
}

function commitTimeInput() {
  const parsed = parseHHMM(timeText.value);
  if (parsed != null) draftTime.value = parsed;
  timeText.value = draftTime.value != null ? minutesToHHMM(draftTime.value) : '';
}

function goToday() {
  draftDate.value = today(getLocalTimeZone());
}

function applyDraft() {
  if (!draftDate.value) {
    model.value = undefined;
  } else if (draftTime.value != null) {
    model.value = new CalendarDateTime(
      draftDate.value.year,
      draftDate.value.month,
      draftDate.value.day,
      Math.floor(draftTime.value / 60),
      draftTime.value % 60,
    );
  } else {
    model.value = new CalendarDate(
      draftDate.value.year,
      draftDate.value.month,
      draftDate.value.day,
    );
  }
  open.value = false;
}

function cancel() {
  open.value = false;
}

function clear() {
  model.value = undefined;
}

const fadeMask = {
  maskImage:
    'linear-gradient(to bottom, transparent, #000 20px, #000 calc(100% - 20px), transparent)',
  WebkitMaskImage:
    'linear-gradient(to bottom, transparent, #000 20px, #000 calc(100% - 20px), transparent)',
};
</script>

<template>
  <div :class="cn('flex flex-col gap-1.5', props.class)">
    <Label v-if="label" :for="fieldId" :required="required" :disabled="disabled">
      {{ label }}
    </Label>

    <Popover v-model:open="open">
      <PopoverAnchor
        as-child
        :class="[
          inputWrapperVariants({ status: resolvedStatus }),
          'flex items-center gap-2 px-3 py-1.5',
          disabled && 'pointer-events-none bg-muted opacity-60',
        ]"
      >
        <div>
          <PopoverTrigger as-child>
            <button
              :id="fieldId"
              v-bind="$attrs"
              type="button"
              :disabled="disabled"
              :aria-invalid="resolvedStatus === 'error' || undefined"
              :aria-describedby="message ? messageId : undefined"
              class="flex min-w-0 flex-1 cursor-pointer items-center gap-2 text-left outline-none"
            >
              <CalendarIcon class="size-4 shrink-0 opacity-70" />
              <span class="min-w-0 flex-1 truncate" :class="!formatted && 'text-muted-foreground'">
                {{ formatted || placeholder }}
              </span>
            </button>
          </PopoverTrigger>

          <button
            v-if="clearable && model && !disabled"
            type="button"
            :aria-label="clearLabel"
            class="shrink-0 cursor-pointer rounded-sm opacity-60 transition-opacity hover:opacity-100 focus-visible:opacity-100 focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none"
            @click="clear"
          >
            <CircleX class="size-5" />
          </button>
        </div>
      </PopoverAnchor>

      <PopoverContent class="w-auto overflow-hidden p-0" align="start">
        <div class="flex max-sm:flex-col">
          <div class="p-3 sm:grow">
            <Calendar
              v-model="calendarModel"
              :locale="locale"
              :week-starts-on="weekStartsOn"
              :min-value="minValue"
              :max-value="maxValue"
              :is-date-disabled="isDateDisabled"
              :is-date-unavailable="isDateUnavailable"
            />
          </div>

          <div v-if="showTime" class="w-40 self-stretch border-border max-sm:border-t sm:border-l">
            <ScrollArea class="h-full max-h-80" :viewport-style="fadeMask">
              <div class="flex flex-col gap-1 p-2">
                <button
                  v-for="minute in slotMinutes"
                  :key="minute"
                  type="button"
                  :class="
                    cn(
                      'rounded-md border px-3 py-2 text-center text-body-sm tabular-nums transition-colors focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none',
                      draftTime === minute
                        ? 'border-primary-text bg-primary-soft text-primary-soft-foreground'
                        : 'border-border text-foreground hover:bg-accent',
                    )
                  "
                  @click="draftTime = minute"
                >
                  {{ formatSlot(minute) }}
                </button>
              </div>
            </ScrollArea>
          </div>
        </div>

        <div
          v-if="showTime"
          class="flex items-center justify-between gap-2 border-t border-border p-3"
        >
          <div class="flex items-center gap-2">
            <input
              v-model="dateText"
              type="text"
              inputmode="numeric"
              placeholder="DD/MM/YYYY"
              class="w-32 rounded-md border border-border bg-transparent h-8 px-2.5 text-body-sm tabular-nums text-foreground outline-none transition-colors focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/30"
              @change="commitDateInput"
            />
            <input
              v-model="timeText"
              type="text"
              inputmode="numeric"
              placeholder="HH:MM"
              class="w-20 rounded-md border border-border bg-transparent h-8 px-2.5 text-body-sm tabular-nums text-foreground outline-none transition-colors focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/30"
              @change="commitTimeInput"
            />
            <Button variant="outline" size="sm" @click="goToday">
              {{ todayLabel }}
            </Button>
          </div>
          <div class="flex items-center gap-2">
            <Button variant="outline" size="sm" @click="cancel">
              {{ cancelLabel }}
            </Button>
            <Button size="sm" @click="applyDraft">{{ applyLabel }}</Button>
          </div>
        </div>
      </PopoverContent>
    </Popover>

    <FieldMessage v-if="message" :id="messageId" :status="resolvedStatus">
      {{ message }}
    </FieldMessage>
  </div>
</template>
