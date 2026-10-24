<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { SelectContent, SelectPortal, SelectViewport, type SelectContentProps } from 'reka-ui';
import { cn } from '@/lib/utils';

interface Props {
  position?: SelectContentProps['position'];
  sideOffset?: number;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  position: 'popper',
  sideOffset: 6,
});

defineOptions({ inheritAttrs: false });
</script>

<template>
  <SelectPortal>
    <SelectContent
      v-bind="$attrs"
      :position="position"
      :side-offset="sideOffset"
      :class="
        cn(
          'z-50 max-h-60 min-w-(--reka-select-trigger-width) origin-(--reka-select-content-transform-origin) overflow-hidden rounded-lg border border-border bg-popover text-body-sm text-popover-foreground shadow-l data-[state=open]:animate-overlay-in data-[state=closed]:animate-overlay-out',
          props.class,
        )
      "
    >
      <SelectViewport class="p-1">
        <slot />
      </SelectViewport>
    </SelectContent>
  </SelectPortal>
</template>
