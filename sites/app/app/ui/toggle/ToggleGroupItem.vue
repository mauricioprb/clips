<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { ToggleGroupItem } from 'reka-ui';
import { cn } from '@/lib/utils';
import { toggleVariants } from '.';
import { useToggleGroupContext } from './context';

interface Props {
  value: string;
  disabled?: boolean;
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();

defineOptions({ inheritAttrs: false });

const context = useToggleGroupContext();
</script>

<template>
  <ToggleGroupItem
    v-bind="$attrs"
    :value="props.value"
    :disabled="props.disabled"
    :class="cn(toggleVariants({ variant: context.variant, size: context.size }), props.class)"
  >
    <slot />
  </ToggleGroupItem>
</template>
