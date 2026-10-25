<script setup lang="ts">
import { computed, ref, useId, type HTMLAttributes } from 'vue';
import { CircleX, Clock } from '@lucide/vue';
import { Time } from '@internationalized/date';
import { TimeFieldInput, TimeFieldRoot, type TimeValue } from 'reka-ui';
import { cn } from '@/lib/utils';
import { Label } from '@/ui/label';
import { FieldMessage } from '@/ui/field-message';
import { inputWrapperVariants, type InputWrapperVariants } from '@/ui/input';
import { Popover, PopoverAnchor, PopoverTrigger, PopoverContent } from '@/ui/popover';
import { ScrollArea } from '@/ui/scroll-area';
import { useFuseLocale } from '@/lib/locale';

interface Props {
  label?: string;
  hint?: string;
  error?: string;
  required?: boolean;
  status?: InputWrapperVariants['status'];
  disabled?: boolean;
  clearable?: boolean;
  clearLabel?: string;
  openLabel?: string;
  locale?: string;
  hourCycle?: 12 | 24;
  min?: string;
  max?: string;
  step?: number;
  name?: string;
  id?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  status: 'default',
  clearLabel: () => useFuseLocale().timePicker.clearLabel,
  openLabel: () => useFuseLocale().timePicker.openLabel,
  locale: () => useFuseLocale().dateLocale,
  min: '00:00',
  max: '23:59',
  step: 30,
});

const model = defineModel<string>();

defineOptions({ inheritAttrs: false });

const open = ref(false);
const list = ref<HTMLElement>();

const autoId = useId();
const fieldId = computed(() => props.id ?? autoId);
const messageId = computed(() => `${fieldId.value}-message`);
const resolvedStatus = computed(() => (props.error ? 'error' : props.status));
const message = computed(() => props.error ?? props.hint);

const pad = (value: number) => String(value).padStart(2, '0');

const toMinutes = (value: string) => {
  const [hour, minute] = value.split(':').map(Number);
  return (hour ?? 0) * 60 + (minute || 0);
};

const toClock = (minutes: number) => `${pad(Math.floor(minutes / 60))}:${pad(minutes % 60)}`;

const toTime = (value: string) => {
  const minutes = toMinutes(value);
  return new Time(Math.floor(minutes / 60), minutes % 60);
};

const timeModel = computed<TimeValue | undefined>({
  get: () => (model.value ? toTime(model.value) : undefined),
  set: (value) => {
    model.value = value ? `${pad(value.hour)}:${pad(value.minute)}` : undefined;
  },
});

const slots = computed(() => {
  const end = toMinutes(props.max);
  const minutes: number[] = [];
  for (let minute = toMinutes(props.min); minute <= end; minute += props.step) {
    minutes.push(minute);
  }
  return minutes;
});

const selectedMinutes = computed(() => (model.value ? toMinutes(model.value) : undefined));

const slotFormatter = computed(
  () =>
    new Intl.DateTimeFormat(props.locale, {
      hour: '2-digit',
      minute: '2-digit',
      hourCycle: props.hourCycle === 12 ? 'h12' : props.hourCycle && 'h23',
    }),
);

const formatSlot = (minutes: number) =>
  slotFormatter.value.format(new Date(2000, 0, 1, Math.floor(minutes / 60), minutes % 60));

function select(minutes: number) {
  model.value = toClock(minutes);
  open.value = false;
}

function focusSelected(event: Event) {
  const target =
    list.value?.querySelector<HTMLElement>('[data-selected]') ??
    list.value?.querySelector<HTMLElement>('button');
  if (!target) return;
  event.preventDefault();
  target.focus();
}

const fadeMask = {
  maskImage:
    'linear-gradient(to bottom, transparent, #000 12px, #000 calc(100% - 12px), transparent)',
  WebkitMaskImage:
    'linear-gradient(to bottom, transparent, #000 12px, #000 calc(100% - 12px), transparent)',
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
          <TimeFieldRoot
            :id="fieldId"
            v-slot="{ segments }"
            v-model="timeModel"
            v-bind="$attrs"
            :locale="locale"
            :hour-cycle="hourCycle"
            :step="{ minute: step }"
            :min-value="toTime(min)"
            :max-value="toTime(max)"
            :name="name"
            :required="required"
            :disabled="disabled"
            :aria-invalid="resolvedStatus === 'error' || undefined"
            :aria-describedby="message ? messageId : undefined"
            class="flex min-w-0 flex-1 items-center tabular-nums"
          >
            <template v-for="item in segments" :key="item.part">
              <TimeFieldInput
                v-if="item.part === 'literal'"
                :part="item.part"
                class="text-muted-foreground"
              >
                {{ item.value }}
              </TimeFieldInput>
              <TimeFieldInput
                v-else
                :part="item.part"
                class="rounded-sm px-0.5 outline-none focus:bg-primary-soft focus:text-primary-soft-foreground data-placeholder:text-muted-foreground"
              >
                {{ item.value }}
              </TimeFieldInput>
            </template>
          </TimeFieldRoot>

          <button
            v-if="clearable && model && !disabled"
            type="button"
            :aria-label="clearLabel"
            class="shrink-0 cursor-pointer rounded-sm opacity-60 transition-opacity hover:opacity-100 focus-visible:opacity-100 focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none"
            @click="model = undefined"
          >
            <CircleX class="size-5" />
          </button>

          <PopoverTrigger as-child>
            <button
              type="button"
              :disabled="disabled"
              :aria-label="openLabel"
              class="-mr-1 shrink-0 cursor-pointer rounded-sm p-1 opacity-70 transition-opacity hover:opacity-100 focus-visible:opacity-100 focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none"
            >
              <Clock class="size-4" />
            </button>
          </PopoverTrigger>
        </div>
      </PopoverAnchor>

      <PopoverContent
        class="w-(--reka-popover-trigger-width) min-w-36 overflow-hidden p-0"
        align="end"
        @open-auto-focus="focusSelected"
      >
        <ScrollArea viewport-class="max-h-64" :viewport-style="fadeMask">
          <div ref="list" class="flex flex-col gap-1 p-2">
            <button
              v-for="minutes in slots"
              :key="minutes"
              type="button"
              :data-selected="selectedMinutes === minutes || undefined"
              :class="
                cn(
                  'rounded-md border px-3 py-1.5 text-center text-body-sm tabular-nums transition-colors focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none',
                  selectedMinutes === minutes
                    ? 'border-primary-text bg-primary-soft text-primary-soft-foreground'
                    : 'border-border text-foreground hover:bg-accent',
                )
              "
              @click="select(minutes)"
            >
              {{ formatSlot(minutes) }}
            </button>
          </div>
        </ScrollArea>
      </PopoverContent>
    </Popover>

    <FieldMessage v-if="message" :id="messageId" :status="resolvedStatus">
      {{ message }}
    </FieldMessage>
  </div>
</template>
