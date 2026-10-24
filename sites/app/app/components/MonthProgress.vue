<script setup lang="ts">
import { Progress } from '~/ui/progress';

const props = defineProps<{
  workdayMinutes: number;
  targetMinutes: number;
  loggedMinutes: number;
  workdays: number;
  dailyTargetMinutes: number;
}>();

const percent = computed(() =>
  props.targetMinutes > 0
    ? Math.min(Math.round((props.workdayMinutes / props.targetMinutes) * 100), 100)
    : 0,
);
const outsideMinutes = computed(() => Math.max(props.loggedMinutes - props.workdayMinutes, 0));
</script>

<template>
  <div class="flex flex-col gap-2 text-body-sm text-muted-foreground">
    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
      <p>
        <strong class="font-semibold text-foreground tabular-nums">{{
          formatDuration(workdayMinutes)
        }}</strong>
        de {{ formatDuration(targetMinutes) }} no relatório
      </p>
      <Progress
        :model-value="percent"
        size="sm"
        :intent="percent >= 100 ? 'success' : 'primary'"
        :get-value-label="(value) => `${value ?? 0}% da meta do mês`"
        class="w-24"
        aria-label="Progresso da meta do mês"
      />
      <p class="text-caption sm:ml-auto">
        {{ workdays }} dias úteis, {{ formatDuration(dailyTargetMinutes) }} por dia
      </p>
    </div>
    <p v-if="outsideMinutes > 0" class="text-caption">
      {{ formatDuration(outsideMinutes) }} em fins de semana e feriados ficam fora do relatório.
    </p>
  </div>
</template>
