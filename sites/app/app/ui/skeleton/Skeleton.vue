<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { Primitive, type PrimitiveProps } from 'reka-ui';
import { cn } from '@/lib/utils';
import { skeletonVariants, type SkeletonVariants } from '.';

interface Props extends PrimitiveProps {
  variant?: SkeletonVariants['variant'];
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  as: 'div',
});
</script>

<template>
  <Primitive
    :as="as"
    :as-child="asChild"
    aria-hidden="true"
    :class="cn(skeletonVariants({ variant }), props.class)"
  >
    <slot />
  </Primitive>
</template>
