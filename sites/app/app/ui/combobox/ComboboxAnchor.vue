<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { ComboboxAnchor } from 'reka-ui';
import { cn } from '@/lib/utils';
import { inputWrapperVariants, type InputWrapperVariants } from '@/ui/input';

interface Props {
  status?: InputWrapperVariants['status'];
  disabled?: boolean;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  status: 'default',
});
</script>

<template>
  <ComboboxAnchor
    :class="
      cn(
        inputWrapperVariants({ status }),
        'items-center gap-2 px-3 py-1.5',
        disabled && 'pointer-events-none bg-muted opacity-60',
        props.class,
      )
    "
  >
    <slot />
  </ComboboxAnchor>
</template>
