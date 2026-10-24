<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { DropdownMenuContent, DropdownMenuPortal } from 'reka-ui';
import { cn } from '@/lib/utils';

interface Props {
  sideOffset?: number;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  sideOffset: 6,
});

defineOptions({ inheritAttrs: false });
</script>

<template>
  <DropdownMenuPortal>
    <DropdownMenuContent
      v-bind="$attrs"
      :side-offset="sideOffset"
      :class="
        cn(
          'z-50 min-w-48 origin-(--reka-dropdown-menu-content-transform-origin) overflow-hidden rounded-lg border border-border bg-popover p-1 text-popover-foreground shadow-l data-[state=open]:animate-overlay-in data-[state=closed]:animate-overlay-out',
          props.class,
        )
      "
    >
      <slot />
    </DropdownMenuContent>
  </DropdownMenuPortal>
</template>
