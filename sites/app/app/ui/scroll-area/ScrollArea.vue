<script setup lang="ts">
import { ref, type HTMLAttributes, type StyleValue } from 'vue';
import {
  ScrollAreaRoot,
  ScrollAreaViewport,
  ScrollAreaCorner,
  type ScrollAreaRootProps,
} from 'reka-ui';
import { cn } from '@/lib/utils';
import ScrollBar from './ScrollBar.vue';

interface Props {
  type?: ScrollAreaRootProps['type'];
  orientation?: 'vertical' | 'horizontal' | 'both';
  scrollHideDelay?: number;
  class?: HTMLAttributes['class'];
  viewportClass?: HTMLAttributes['class'];
  viewportStyle?: StyleValue;
}

const props = withDefaults(defineProps<Props>(), {
  type: 'hover',
  orientation: 'vertical',
  scrollHideDelay: 600,
});

const viewportRef = ref<{ viewportElement?: HTMLElement | null } | null>(null);

defineExpose({
  getViewport: () => viewportRef.value?.viewportElement ?? undefined,
});
</script>

<template>
  <ScrollAreaRoot
    :type="type"
    :scroll-hide-delay="scrollHideDelay"
    :class="cn('relative overflow-hidden', props.class)"
  >
    <ScrollAreaViewport
      ref="viewportRef"
      :class="cn('size-full rounded-[inherit]', viewportClass)"
      :style="viewportStyle"
    >
      <slot />
    </ScrollAreaViewport>
    <ScrollBar v-if="orientation === 'vertical' || orientation === 'both'" orientation="vertical" />
    <ScrollBar
      v-if="orientation === 'horizontal' || orientation === 'both'"
      orientation="horizontal"
    />
    <ScrollAreaCorner />
  </ScrollAreaRoot>
</template>
