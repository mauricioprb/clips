<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { ComboboxInput } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps<{ class?: HTMLAttributes['class'] }>();

defineOptions({ inheritAttrs: false });
</script>

<template>
  <ComboboxInput
    v-bind="$attrs"
    :class="
      cn(
        'min-w-24 flex-1 bg-transparent outline-none placeholder:text-muted-foreground',
        props.class,
      )
    "
  />
</template>
