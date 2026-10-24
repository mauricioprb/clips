<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
  selected?: boolean;
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
  <tr
    :data-state="selected ? 'selected' : undefined"
    :class="
      cn(
        'border-b border-border transition-colors duration-150 ease-out-quart hover:bg-muted/50 data-[state=selected]:bg-primary-soft/40',
        props.class,
      )
    "
  >
    <slot />
  </tr>
</template>
