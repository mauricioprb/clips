<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { Label } from 'reka-ui';
import { cn } from '@/lib/utils';

interface Props {
  for?: string;
  required?: boolean;
  disabled?: boolean;
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
  <Label
    :for="props.for"
    :class="
      cn(
        'inline-flex items-center gap-0.5 text-body-sm font-medium select-none',
        disabled && 'opacity-50',
        props.class,
      )
    "
  >
    <slot />
  </Label>
</template>
