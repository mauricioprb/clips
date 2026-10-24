<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
  src: string;
  alt?: string;
  inset?: boolean;
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
  <img
    :src="src"
    :alt="alt ?? ''"
    :class="cn('w-full object-cover', inset && 'absolute inset-0 -z-10 size-full', props.class)"
  />
</template>
