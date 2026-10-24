<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { PanelLeft } from '@lucide/vue';
import { Primitive, type PrimitiveProps } from 'reka-ui';
import { cn } from '@/lib/utils';
import { useSidebar } from './context';
import { useFuseLocale } from '@/lib/locale';

interface Props extends PrimitiveProps {
  label?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  as: 'button',
  label: () => useFuseLocale().sidebarTrigger.label,
});

const { toggle } = useSidebar();
</script>

<template>
  <Primitive
    :as="as"
    :as-child="asChild"
    type="button"
    data-sidebar-trigger
    :aria-label="label"
    :class="
      cn(
        'inline-flex size-8 shrink-0 items-center justify-center rounded-md text-muted-foreground outline-none transition-colors duration-150 ease-out-quart cursor-pointer hover:bg-secondary hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:ring-offset-1 focus-visible:ring-offset-background [&_svg]:size-5',
        props.class,
      )
    "
    @click="toggle"
  >
    <slot>
      <PanelLeft />
    </slot>
  </Primitive>
</template>
