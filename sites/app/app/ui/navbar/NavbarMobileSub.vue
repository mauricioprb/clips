<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
  <div
    class="*:animate-collapsible-item [&>*:nth-child(1)]:[--collapsible-stagger:80ms] [&>*:nth-child(2)]:[--collapsible-stagger:120ms] [&>*:nth-child(3)]:[--collapsible-stagger:160ms] [&>*:nth-child(4)]:[--collapsible-stagger:200ms] [&>*:nth-child(5)]:[--collapsible-stagger:240ms] [&>*:nth-child(6)]:[--collapsible-stagger:280ms]"
    :class="cn('mt-1 ml-5.5 flex flex-col gap-1 border-l border-border py-1 pl-3', props.class)"
  >
    <slot />
  </div>
</template>
