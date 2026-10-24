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
    data-sidebar-header
    :class="
      cn(
        'flex items-center gap-2 px-4 py-4 group-data-[collapsed=true]/sidebar:justify-center group-data-[collapsed=true]/sidebar:px-2',
        props.class,
      )
    "
  >
    <slot />
  </Primitive>
</template>
