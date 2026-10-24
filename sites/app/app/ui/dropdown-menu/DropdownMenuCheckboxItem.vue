<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { DropdownMenuCheckboxItem, DropdownMenuItemIndicator } from 'reka-ui';
import { Check } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { dropdownMenuItemVariants } from '.';

const props = defineProps<{ class?: HTMLAttributes['class'] }>();

defineOptions({ inheritAttrs: false });
</script>

<template>
  <DropdownMenuCheckboxItem
    v-bind="$attrs"
    :class="cn(dropdownMenuItemVariants(), 'pl-8', props.class)"
  >
    <span class="absolute left-2 inline-flex size-4 items-center justify-center">
      <DropdownMenuItemIndicator>
        <Check />
      </DropdownMenuItemIndicator>
    </span>
    <slot />
  </DropdownMenuCheckboxItem>
</template>
