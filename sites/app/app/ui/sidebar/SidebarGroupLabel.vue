<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { Primitive, type PrimitiveProps } from 'reka-ui';
import { cn } from '@/lib/utils';

interface Props extends PrimitiveProps {
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
    :class="
      cn(
        'px-3 pb-1 text-caption font-medium whitespace-nowrap text-muted-foreground group-data-[collapsed=true]/sidebar:hidden',
        props.class,
      )
    "
  >
    <slot />
  </Primitive>
</template>
