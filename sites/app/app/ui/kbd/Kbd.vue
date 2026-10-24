<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { Primitive, type PrimitiveProps } from 'reka-ui';
import { cn } from '@/lib/utils';
import { kbdVariants, type KbdVariants } from '.';

interface Props extends PrimitiveProps {
  size?: KbdVariants['size'];
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  as: 'kbd',
});
</script>

<template>
  <Primitive :as="as" :as-child="asChild" :class="cn(kbdVariants({ size }), props.class)">
    <slot />
  </Primitive>
</template>
