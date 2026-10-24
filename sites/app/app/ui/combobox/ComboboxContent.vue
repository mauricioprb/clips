<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import {
  ComboboxContent,
  ComboboxPortal,
  ComboboxViewport,
  type ComboboxContentProps,
} from 'reka-ui';
import { cn } from '@/lib/utils';

interface Props {
  position?: ComboboxContentProps['position'];
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
  <ComboboxPortal>
    <ComboboxContent
      v-bind="$attrs"
      :position="position"
      :side-offset="sideOffset"
      :class="
        cn(
          'z-50 max-h-60 min-w-(--reka-combobox-trigger-width) origin-(--reka-combobox-content-transform-origin) overflow-hidden rounded-lg border border-border bg-popover text-body-sm text-popover-foreground shadow-l data-[state=open]:animate-overlay-in data-[state=closed]:animate-overlay-out',
          props.class,
        )
      "
    >
      <ComboboxViewport class="p-1">
        <slot />
      </ComboboxViewport>
    </ComboboxContent>
  </ComboboxPortal>
</template>
