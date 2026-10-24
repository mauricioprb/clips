<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { Primitive, type PrimitiveProps } from 'reka-ui';
import { cn } from '@/lib/utils';
import { cardVariants, type CardVariants } from '.';

interface Props extends PrimitiveProps {
  variant?: CardVariants['variant'];
  hover?: CardVariants['hover'];
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  as: 'div',
  variant: 'default',
  hover: false,
});
</script>

<template>
  <Primitive
    :as="as"
    :as-child="asChild"
    :data-variant="variant"
    :class="cn(cardVariants({ variant, hover }), props.class)"
  >
    <slot />
  </Primitive>
</template>
