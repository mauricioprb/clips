<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { SelectItem, SelectItemIndicator, SelectItemText, type SelectItemProps } from 'reka-ui';
import { Check } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { selectItemVariants } from '.';

interface Props extends SelectItemProps {
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();

defineOptions({ inheritAttrs: false });
</script>

<template>
  <SelectItem
    v-bind="$attrs"
    :value="props.value"
    :disabled="props.disabled"
    :text-value="props.textValue"
    :class="cn(selectItemVariants(), props.class)"
  >
    <SelectItemIndicator class="absolute left-2 inline-flex">
      <Check class="size-4" />
    </SelectItemIndicator>
    <SelectItemText>
      <slot />
    </SelectItemText>
  </SelectItem>
</template>
