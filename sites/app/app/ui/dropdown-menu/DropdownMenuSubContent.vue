<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { DropdownMenuSubContent, DropdownMenuPortal } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps<{ class?: HTMLAttributes['class'] }>();

defineOptions({ inheritAttrs: false });
</script>

<template>
  <DropdownMenuPortal>
    <DropdownMenuSubContent
      v-bind="$attrs"
      :class="
        cn(
          'z-50 min-w-44 origin-(--reka-dropdown-menu-content-transform-origin) overflow-hidden rounded-lg border border-border bg-popover p-1 text-popover-foreground shadow-l data-[state=open]:animate-overlay-in data-[state=closed]:animate-overlay-out',
          props.class,
        )
      "
    >
      <slot />
    </DropdownMenuSubContent>
  </DropdownMenuPortal>
</template>
