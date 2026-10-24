<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { ScrollAreaScrollbar, ScrollAreaThumb } from 'reka-ui';
import { cn } from '@/lib/utils';
import { scrollBarVariants } from '.';

interface Props {
  orientation?: 'vertical' | 'horizontal';
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  orientation: 'vertical',
});
</script>

<template>
  <ScrollAreaScrollbar
    :orientation="orientation"
    :class="cn(scrollBarVariants({ orientation }), props.class)"
  >
    <ScrollAreaThumb
      class="relative flex-1 rounded-full bg-muted-foreground/20 transition-colors duration-150 ease-out-quart hover:bg-muted-foreground/30"
    />
  </ScrollAreaScrollbar>
</template>
