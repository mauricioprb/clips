<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { Primitive, type PrimitiveProps } from 'reka-ui';
import { cn } from '@/lib/utils';
import { sidebarMenuSubButtonVariants } from '.';

interface Props extends PrimitiveProps {
  active?: boolean;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  as: 'button',
  active: false,
});
</script>

<template>
  <Primitive
    :as="as"
    :as-child="asChild"
    :data-active="active"
    :aria-current="active ? 'page' : undefined"
    :class="cn(sidebarMenuSubButtonVariants(), props.class)"
  >
    <slot />
  </Primitive>
</template>
