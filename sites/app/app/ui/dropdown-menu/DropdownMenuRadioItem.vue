<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { DropdownMenuRadioItem, DropdownMenuItemIndicator, type AcceptableValue } from 'reka-ui';
import { cn } from '@/lib/utils';
import { dropdownMenuItemVariants } from '.';

interface Props {
  value: AcceptableValue;
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();

defineOptions({ inheritAttrs: false });
</script>

<template>
  <DropdownMenuRadioItem
    v-bind="$attrs"
    :value="value"
    :class="cn(dropdownMenuItemVariants(), 'pl-8', props.class)"
  >
    <span class="absolute left-2 inline-flex size-4 items-center justify-center">
      <DropdownMenuItemIndicator>
        <span class="size-1.5 rounded-full bg-current" />
      </DropdownMenuItemIndicator>
    </span>
    <slot />
  </DropdownMenuRadioItem>
</template>
