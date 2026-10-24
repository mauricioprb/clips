<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import { ScrollArea } from '@/ui/scroll-area';

interface Props {
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
  <ScrollArea :class="cn('min-h-0 flex-1', props.class)">
    <div class="flex flex-col gap-5 px-3 py-2 group-data-[collapsed=true]/sidebar:px-0">
      <slot />
    </div>
  </ScrollArea>
</template>
