<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { Primitive, type PrimitiveProps } from 'reka-ui';
import { cn } from '@/lib/utils';
import { Tooltip, TooltipTrigger, TooltipContent } from '@/ui/tooltip';
import { sidebarMenuButtonVariants, type SidebarMenuButtonVariants } from '.';
import { useSidebar, useSidebarMenuContext } from './context';

interface Props extends PrimitiveProps {
  active?: boolean;
  variant?: SidebarMenuButtonVariants['variant'];
  size?: SidebarMenuButtonVariants['size'];
  tooltip?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  as: 'button',
  active: false,
});

defineOptions({ inheritAttrs: false });

const { collapsed, isMobile } = useSidebar();
const menu = useSidebarMenuContext();

const variant = computed(() => props.variant ?? menu.variant.value);

const showTooltip = computed(() => Boolean(props.tooltip) && collapsed.value && !isMobile.value);

const buttonClass = computed(() =>
  cn(sidebarMenuButtonVariants({ variant: variant.value, size: props.size }), props.class),
);
</script>

<template>
  <Tooltip v-if="showTooltip">
    <TooltipTrigger as-child>
      <Primitive
        v-bind="$attrs"
        :as="as"
        :as-child="asChild"
        :data-active="active"
        :aria-current="active ? 'page' : undefined"
        :class="buttonClass"
      >
        <slot />
      </Primitive>
    </TooltipTrigger>
    <TooltipContent variant="light" side="right">{{ tooltip }}</TooltipContent>
  </Tooltip>

  <Primitive
    v-else
    v-bind="$attrs"
    :as="as"
    :as-child="asChild"
    :data-active="active"
    :aria-current="active ? 'page' : undefined"
    :class="buttonClass"
  >
    <slot />
  </Primitive>
</template>
