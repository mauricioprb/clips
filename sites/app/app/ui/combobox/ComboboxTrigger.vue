<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { ComboboxTrigger } from 'reka-ui';
import { ChevronDown } from '@lucide/vue';
import { cn } from '@/lib/utils';

const props = defineProps<{ class?: HTMLAttributes['class'] }>();

defineOptions({ inheritAttrs: false });
</script>

<template>
  <ComboboxTrigger
    v-bind="$attrs"
    :class="
      cn(
        'inline-flex shrink-0 items-center opacity-70 transition-transform duration-200 ease-out-quart data-[state=open]:rotate-180',
        props.class,
      )
    "
  >
    <slot>
      <ChevronDown class="size-4" />
    </slot>
  </ComboboxTrigger>
</template>
