<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import { eventChipVariants, type EventChipVariants } from '.';
import type { CalendarEvent } from './types';
import { formatTime, toDate } from './utils';
import { useFuseLocale } from '@/lib/locale';

interface Props {
  event: CalendarEvent;
  as?: 'button' | 'span';
  block?: boolean;
  variant?: EventChipVariants['variant'];
  locale?: string;
  showTime?: boolean;
  responsiveTime?: boolean;
  timeLabel?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  as: 'button',
  block: false,
  variant: 'soft',
  locale: () => useFuseLocale().dateLocale,
  showTime: true,
  responsiveTime: false,
});

const color = computed(() => props.event.color ?? 'blue');

const time = computed(() => {
  if (props.timeLabel) return props.timeLabel;
  if (props.event.allDay) return '';
  return formatTime(toDate(props.event.start), props.locale);
});
</script>

<template>
  <component
    :is="as"
    :type="as === 'button' ? 'button' : undefined"
    :title="event.title"
    :class="cn(eventChipVariants({ variant, color, block }), props.class)"
  >
    <template v-if="block">
      <span class="line-clamp-2 w-full font-semibold leading-tight">
        {{ event.title }}
      </span>
      <span v-if="showTime && time" class="text-[0.6875rem]">
        {{ time }}
      </span>
    </template>
    <template v-else>
      <span class="truncate font-medium">{{ event.title }}</span>
      <span
        v-if="showTime && time"
        :class="
          cn(
            'ml-auto shrink-0 pl-1 text-[0.6875rem] tabular-nums',
            responsiveTime && '@max-2xl/cal:hidden',
          )
        "
      >
        {{ time }}
      </span>
    </template>
  </component>
</template>
