<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { PopoverContent, PopoverPortal, type PopoverContentProps } from 'reka-ui';
import { cn } from '@/lib/utils';
import { popoverContentVariants } from '.';

interface Props {
  side?: PopoverContentProps['side'];
  sideOffset?: number;
  align?: PopoverContentProps['align'];
  alignOffset?: number;
  arrow?: boolean;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  sideOffset: 8,
  align: 'center',
  arrow: false,
});

defineOptions({ inheritAttrs: false });

const arrowClass = cn(
  'pointer-events-none absolute size-2.5 rotate-45 rounded-[2px] bg-popover border-border',
  'group-data-[side=top]/popover:bottom-0 group-data-[side=top]/popover:left-1/2 group-data-[side=top]/popover:-translate-x-1/2 group-data-[side=top]/popover:translate-y-1/2 group-data-[side=top]/popover:border-r group-data-[side=top]/popover:border-b',
  'group-data-[side=bottom]/popover:top-0 group-data-[side=bottom]/popover:left-1/2 group-data-[side=bottom]/popover:-translate-x-1/2 group-data-[side=bottom]/popover:-translate-y-1/2 group-data-[side=bottom]/popover:border-t group-data-[side=bottom]/popover:border-l',
  'group-data-[side=left]/popover:right-0 group-data-[side=left]/popover:top-1/2 group-data-[side=left]/popover:-translate-y-1/2 group-data-[side=left]/popover:translate-x-1/2 group-data-[side=left]/popover:border-t group-data-[side=left]/popover:border-r',
  'group-data-[side=right]/popover:left-0 group-data-[side=right]/popover:top-1/2 group-data-[side=right]/popover:-translate-y-1/2 group-data-[side=right]/popover:-translate-x-1/2 group-data-[side=right]/popover:border-b group-data-[side=right]/popover:border-l',
);
</script>

<template>
  <PopoverPortal>
    <PopoverContent
      v-bind="$attrs"
      :side="side"
      :side-offset="sideOffset"
      :align="align"
      :align-offset="alignOffset"
      :class="cn('group/popover', popoverContentVariants(), props.class)"
    >
      <slot />
      <span v-if="arrow" :class="arrowClass" />
    </PopoverContent>
  </PopoverPortal>
</template>
