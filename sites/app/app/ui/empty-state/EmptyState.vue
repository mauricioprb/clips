<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { Primitive, type PrimitiveProps } from 'reka-ui';
import { cn } from '@/lib/utils';
import { emptyStateVariants, emptyStateMediaVariants, type EmptyStateVariants } from '.';

interface Props extends PrimitiveProps {
  title?: string;
  description?: string;
  size?: EmptyStateVariants['size'];
  bordered?: EmptyStateVariants['bordered'];
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  as: 'div',
  size: 'md',
});
</script>

<template>
  <Primitive
    :as="as"
    :as-child="asChild"
    :class="cn(emptyStateVariants({ size, bordered }), props.class)"
  >
    <div v-if="$slots.icon" :class="emptyStateMediaVariants({ size })">
      <slot name="icon" />
    </div>

    <div class="flex max-w-md flex-col gap-1">
      <h3 v-if="title || $slots.title" class="text-body-md font-semibold text-foreground">
        <slot name="title">{{ title }}</slot>
      </h3>
      <p v-if="description || $slots.default" class="text-body-sm text-muted-foreground">
        <slot>{{ description }}</slot>
      </p>
    </div>

    <div v-if="$slots.action" class="mt-1 flex flex-wrap items-center justify-center gap-2">
      <slot name="action" />
    </div>
  </Primitive>
</template>
