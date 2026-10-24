<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import { ScrollArea } from '@/ui/scroll-area';

interface Props {
  containerClass?: HTMLAttributes['class'];
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
  <ScrollArea orientation="horizontal" :class="cn('w-full', containerClass)">
    <table :class="cn('w-full caption-bottom border-collapse text-body-sm', props.class)">
      <slot />
    </table>
  </ScrollArea>
</template>
