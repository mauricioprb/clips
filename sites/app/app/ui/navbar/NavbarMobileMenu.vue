<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { CollapsibleContent as CollapsibleContentPrimitive } from 'reka-ui';
import { CollapsibleContent } from '@/ui/collapsible';
import { cn } from '@/lib/utils';
import { useNavbar } from './context';

interface Props {
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();

const { variant } = useNavbar();
const isFloating = computed(() => variant.value === 'floating');
</script>

<template>
  <CollapsibleContentPrimitive
    v-if="isFloating"
    class="absolute inset-x-0 top-full z-50 origin-top desktop:hidden data-[state=open]:animate-overlay-in data-[state=closed]:animate-overlay-out"
  >
    <div
      :class="
        cn(
          'mt-2 flex flex-col gap-1 rounded-2xl border border-border bg-background p-3 shadow-l',
          props.class,
        )
      "
    >
      <slot />
    </div>
  </CollapsibleContentPrimitive>

  <CollapsibleContent v-else class="desktop:hidden">
    <div :class="cn('flex flex-col gap-1 px-3 pt-1 pb-4', props.class)">
      <slot />
    </div>
  </CollapsibleContent>
</template>
