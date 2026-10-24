<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { CollapsibleContent } from 'reka-ui';
import { cn } from '@/lib/utils';

interface Props {
  forceMount?: boolean;
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
  <CollapsibleContent
    :force-mount="forceMount"
    :class="cn('overflow-hidden collapsible-content', props.class)"
  >
    <slot />
  </CollapsibleContent>
</template>

<style>
.collapsible-content[data-state='open'] {
  animation: var(--animate-collapsible-down);
}
.collapsible-content[data-state='closed'] {
  animation: var(--animate-collapsible-up);
}
</style>
