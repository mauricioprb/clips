<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
  <ul
    class="[&>li]:animate-collapsible-item [&>li:nth-child(1)]:[--collapsible-stagger:100ms] [&>li:nth-child(2)]:[--collapsible-stagger:150ms] [&>li:nth-child(3)]:[--collapsible-stagger:200ms] [&>li:nth-child(4)]:[--collapsible-stagger:250ms] [&>li:nth-child(5)]:[--collapsible-stagger:300ms] [&>li:nth-child(6)]:[--collapsible-stagger:350ms]"
    :class="
      cn(
        'ml-5.5 flex list-none flex-col gap-1 border-l border-border py-1 pl-3 group-data-[collapsed=true]/sidebar:hidden',
        props.class,
      )
    "
  >
    <slot />
  </ul>
</template>
