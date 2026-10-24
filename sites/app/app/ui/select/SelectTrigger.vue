<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { SelectIcon, SelectTrigger } from 'reka-ui';
import { ChevronDown } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { inputWrapperVariants, type InputWrapperVariants } from '@/ui/input';

interface Props {
  status?: InputWrapperVariants['status'];
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  status: 'default',
});

defineOptions({ inheritAttrs: false });
</script>

<template>
  <SelectTrigger
    v-bind="$attrs"
    :class="
      cn(
        inputWrapperVariants({ status }),
        'group items-center gap-2 px-3 py-1.5 text-left outline-none data-disabled:pointer-events-none data-disabled:bg-muted data-disabled:opacity-60 data-placeholder:text-muted-foreground',
        props.class,
      )
    "
  >
    <slot />
    <SelectIcon
      class="inline-flex shrink-0 items-center opacity-70 transition-transform duration-200 ease-out-quart group-data-[state=open]:rotate-180"
    >
      <slot name="icon">
        <ChevronDown class="size-4" />
      </slot>
    </SelectIcon>
  </SelectTrigger>
</template>
