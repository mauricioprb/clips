<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { Separator } from 'reka-ui';
import { cn } from '@/lib/utils';

interface Props {
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
  <Separator :class="cn('mx-3 my-2 h-px shrink-0 bg-border', props.class)" :decorative="true" />
</template>
