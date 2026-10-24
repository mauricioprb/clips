<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { Primitive, type PrimitiveProps } from 'reka-ui';
import { cn } from '@/lib/utils';
import { navbarIconButtonVariants } from '.';

interface Props extends PrimitiveProps {
  label?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  as: 'button',
});
</script>

<template>
  <Primitive
    :as="as"
    :as-child="asChild"
    :type="as === 'button' ? 'button' : undefined"
    :aria-label="label"
    :class="cn(navbarIconButtonVariants(), props.class)"
  >
    <slot />
  </Primitive>
</template>
