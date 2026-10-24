<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { CollapsibleTrigger, type PrimitiveProps } from 'reka-ui';
import { cn } from '@/lib/utils';

interface Props extends PrimitiveProps {
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  as: 'button',
});
</script>

<template>
  <CollapsibleTrigger :as="as" :as-child="asChild" :class="cn(props.class)">
    <slot />
  </CollapsibleTrigger>
</template>
