<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { DropdownMenuLabel } from 'reka-ui';
import { cn } from '@/lib/utils';

interface Props {
  inset?: boolean;
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();

defineOptions({ inheritAttrs: false });
</script>

<template>
  <DropdownMenuLabel
    v-bind="$attrs"
    :class="
      cn('px-2 py-1.5 text-caption font-medium text-muted-foreground', inset && 'pl-8', props.class)
    "
  >
    <slot />
  </DropdownMenuLabel>
</template>
