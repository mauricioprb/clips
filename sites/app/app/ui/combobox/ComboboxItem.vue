<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { ComboboxItem, ComboboxItemIndicator, type ComboboxItemProps } from 'reka-ui';
import { Check } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { comboboxItemVariants } from '.';

interface Props extends ComboboxItemProps {
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();

defineOptions({ inheritAttrs: false });
</script>

<template>
  <ComboboxItem
    v-bind="$attrs"
    :value="props.value"
    :disabled="props.disabled"
    :class="cn(comboboxItemVariants(), props.class)"
  >
    <ComboboxItemIndicator class="absolute left-2 inline-flex">
      <Check class="size-4" />
    </ComboboxItemIndicator>
    <slot />
  </ComboboxItem>
</template>
