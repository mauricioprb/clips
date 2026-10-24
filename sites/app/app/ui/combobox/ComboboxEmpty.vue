<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { ComboboxEmpty } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps<{ class?: HTMLAttributes['class'] }>();
</script>

<template>
  <ComboboxEmpty :class="cn('px-2 py-3 text-center text-muted-foreground', props.class)">
    <slot />
  </ComboboxEmpty>
</template>
