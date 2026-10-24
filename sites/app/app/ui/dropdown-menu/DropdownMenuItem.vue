<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { DropdownMenuItem } from 'reka-ui';
import { cn } from '@/lib/utils';
import { dropdownMenuItemVariants, type DropdownMenuItemVariants } from '.';

interface Props {
  inset?: DropdownMenuItemVariants['inset'];
  variant?: DropdownMenuItemVariants['variant'];
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
});

defineOptions({ inheritAttrs: false });
</script>

<template>
  <DropdownMenuItem
    v-bind="$attrs"
    :class="cn(dropdownMenuItemVariants({ inset, variant }), props.class)"
  >
    <slot />
  </DropdownMenuItem>
</template>
