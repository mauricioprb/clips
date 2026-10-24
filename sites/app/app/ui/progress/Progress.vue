<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { ProgressRoot, ProgressIndicator } from 'reka-ui';
import { cn } from '@/lib/utils';
import {
  progressVariants,
  progressIndicatorVariants,
  type ProgressVariants,
  type ProgressIndicatorVariants,
} from '.';

interface Props {
  modelValue?: number | null;
  max?: number;
  size?: ProgressVariants['size'];
  intent?: ProgressIndicatorVariants['intent'];
  getValueLabel?: (value: number | null | undefined, max: number) => string | undefined;
  indicatorClass?: HTMLAttributes['class'];
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  max: 100,
  size: 'md',
  intent: 'primary',
});

const isIndeterminate = computed(() => props.modelValue === null || props.modelValue === undefined);

const percent = computed(() => {
  if (isIndeterminate.value) return 0;
  return Math.min(100, Math.max(0, (props.modelValue! / props.max) * 100));
});
</script>

<template>
  <ProgressRoot
    :model-value="modelValue"
    :max="max"
    :get-value-label="getValueLabel"
    :class="cn(progressVariants({ size }), props.class)"
  >
    <ProgressIndicator
      :class="
        cn(
          progressIndicatorVariants({ intent }),
          isIndeterminate && 'progress-indeterminate w-2/5',
          indicatorClass,
        )
      "
      :style="isIndeterminate ? undefined : { transform: `translateX(-${100 - percent}%)` }"
    />
  </ProgressRoot>
</template>

<style>
.progress-indeterminate {
  animation: var(--animate-progress-indeterminate);
}
</style>
