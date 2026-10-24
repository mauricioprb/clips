<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { DropdownMenuSubTrigger } from 'reka-ui';
import { ChevronRight } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { dropdownMenuItemVariants } from '.';

interface Props {
  inset?: boolean;
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();

defineOptions({ inheritAttrs: false });
</script>

<template>
  <DropdownMenuSubTrigger
    v-bind="$attrs"
    :class="
      cn(
        dropdownMenuItemVariants({ inset }),
        'data-[state=open]:bg-accent data-[state=open]:text-accent-foreground',
        props.class,
      )
    "
  >
    <slot />
    <ChevronRight class="ml-auto" />
  </DropdownMenuSubTrigger>
</template>
