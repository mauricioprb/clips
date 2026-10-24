<script setup lang="ts">
import { Plus } from '@lucide/vue';
import type { WeeklySlot } from '~/composables/useSchedule';
import { Button } from '~/ui/button';

const props = defineProps<{ slots: WeeklySlot[]; weekdays: { value: number; label: string }[] }>();
const emit = defineEmits<{
  create: [weekday: number, startMinute?: number];
  edit: [slot: WeeklySlot];
}>();

const range = computed(() => visibleHours(props.slots));
const hours = computed(() =>
  Array.from(
    { length: range.value.endHour - range.value.startHour },
    (_, index) => range.value.startHour + index,
  ),
);

function slotsOf(weekday: number): WeeklySlot[] {
  return props.slots.filter((slot) => slot.weekday === weekday);
}

function slotStyle(slot: WeeklySlot): Record<string, string> {
  const { top, height } = blockPosition(slot, range.value.startHour);

  return { top: `${top}px`, height: `${Math.max(height, 22)}px` };
}

function createAt(weekday: number, event: MouseEvent) {
  const column = event.currentTarget as HTMLElement;

  emit(
    'create',
    weekday,
    minuteAtOffset(event.clientY - column.getBoundingClientRect().top, range.value.startHour),
  );
}
</script>

<template>
  <div class="overflow-hidden rounded-xl border border-border bg-card">
    <div class="flex border-b border-border">
      <span class="w-14 shrink-0" aria-hidden="true" />
      <div
        v-for="weekday in weekdays"
        :key="weekday.value"
        class="flex min-w-0 flex-1 items-center justify-between border-l border-border py-1.5 pr-1 pl-3"
      >
        <h2 class="text-body-sm font-semibold">{{ weekday.label }}</h2>
        <Button
          type="button"
          variant="ghost"
          size="xs"
          icon-only
          :icon-leading="Plus"
          :aria-label="`Adicionar horário na ${weekday.label.toLowerCase()}`"
          @click="emit('create', weekday.value)"
        />
      </div>
    </div>

    <div class="flex" :style="{ height: `${hours.length * HOUR_HEIGHT}px` }">
      <div class="relative w-14 shrink-0" aria-hidden="true">
        <span
          v-for="(hour, index) in hours"
          :key="hour"
          class="absolute right-2 text-caption text-muted-foreground tabular-nums"
          :class="index === 0 ? 'translate-y-0.5' : '-translate-y-1/2'"
          :style="{ top: `${index * HOUR_HEIGHT}px` }"
        >
          {{ minutesToClock(hour * 60) }}
        </span>
      </div>

      <div
        v-for="weekday in weekdays"
        :key="weekday.value"
        class="relative min-w-0 flex-1 cursor-pointer border-l border-border transition-colors hover:bg-accent"
        title="Clique para adicionar um horário aqui"
        @click="createAt(weekday.value, $event)"
      >
        <span
          v-for="(hour, index) in hours.slice(1)"
          :key="hour"
          class="pointer-events-none absolute inset-x-0 border-t border-border"
          :style="{ top: `${(index + 1) * HOUR_HEIGHT}px` }"
          aria-hidden="true"
        />
        <button
          v-for="slot in slotsOf(weekday.value)"
          :key="slot.id"
          type="button"
          class="absolute inset-x-1 flex cursor-pointer flex-col overflow-hidden rounded-sm border-l-2 border-primary bg-primary-soft px-2 py-1 text-left text-primary-soft-foreground transition-colors hover:bg-primary-soft/80 focus-visible:z-10 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
          :style="slotStyle(slot)"
          :aria-label="`Editar ${slot.description} na ${weekday.label.toLowerCase()}`"
          @click.stop="emit('edit', slot)"
        >
          <span class="text-caption font-semibold tabular-nums">
            {{ minutesToClock(slot.startMinute) }}-{{ minutesToClock(slot.endMinute) }}
          </span>
          <span class="text-caption font-medium">{{ slot.description }}</span>
        </button>
      </div>
    </div>
  </div>
</template>
