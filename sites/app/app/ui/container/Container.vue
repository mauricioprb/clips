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
    :class="cn('mx-auto w-full max-w-[1280px] px-4 tablet:px-8', props.class)"
  >
    <slot />
  </Primitive>
</template>
