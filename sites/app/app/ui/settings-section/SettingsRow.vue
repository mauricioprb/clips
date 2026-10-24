<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import { Label } from '@/ui/label';

interface Props {
  label: string;
  description?: string;
  for?: string;
  layout?: 'inline' | 'stacked';
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  layout: 'inline',
});
</script>

<template>
  <div
    :class="
      cn(
        'flex flex-col gap-3 px-5 py-5 sm:px-6',
        layout === 'inline' && 'sm:flex-row sm:items-center sm:justify-between sm:gap-8',
        props.class,
      )
    "
  >
    <div class="flex min-w-0 flex-col gap-0.5">
      <Label v-if="props.for" :for="props.for" class="text-foreground">
        {{ label }}
      </Label>
      <span v-else class="text-body-sm font-medium text-foreground">
        {{ label }}
      </span>
      <p v-if="description" class="text-body-sm text-muted-foreground">
        {{ description }}
      </p>
    </div>
    <div
      :class="
        cn(
          'flex min-w-0 items-center gap-3',
          layout === 'inline' ? 'sm:shrink-0 sm:justify-end' : 'w-full',
        )
      "
    >
      <slot />
    </div>
  </div>
</template>
