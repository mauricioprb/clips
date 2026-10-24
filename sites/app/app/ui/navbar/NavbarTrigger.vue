<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { CollapsibleTrigger } from 'reka-ui';
import { cn } from '@/lib/utils';
import { navbarIconButtonVariants } from '.';
import { useFuseLocale } from '@/lib/locale';

interface Props {
  label?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  label: () => useFuseLocale().navbarTrigger.label,
});
</script>

<template>
  <CollapsibleTrigger
    :aria-label="label"
    :class="cn(navbarIconButtonVariants(), 'group/navtrigger desktop:hidden', props.class)"
  >
    <slot>
      <span class="relative block h-3.5 w-5">
        <span
          class="absolute top-0 left-0 h-0.5 w-full rounded-full bg-current transition-transform duration-200 ease-out-quart group-data-[state=open]/navtrigger:translate-y-1.5 group-data-[state=open]/navtrigger:rotate-45"
        />
        <span
          class="absolute top-1/2 left-0 h-0.5 w-full -translate-y-1/2 rounded-full bg-current transition-opacity duration-200 ease-out-quart group-data-[state=open]/navtrigger:opacity-0"
        />
        <span
          class="absolute bottom-0 left-0 h-0.5 w-full rounded-full bg-current transition-transform duration-200 ease-out-quart group-data-[state=open]/navtrigger:-translate-y-1.5 group-data-[state=open]/navtrigger:-rotate-45"
        />
      </span>
    </slot>
  </CollapsibleTrigger>
</template>
