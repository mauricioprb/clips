<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { Primitive, type PrimitiveProps } from 'reka-ui';
import { cn } from '@/lib/utils';

interface Props extends PrimitiveProps {
  class?: HTMLAttributes['class'];
}

withDefaults(defineProps<Props>(), {
  as: 'form',
});

const emit = defineEmits<{ submit: [event: Event] }>();

function onSubmit(event: Event) {
  event.preventDefault();
  emit('submit', event);
}
</script>

<template>
  <Primitive
    :as="as"
    :as-child="asChild"
    novalidate
    :class="cn('flex flex-col gap-5', $props.class)"
    @submit="onSubmit"
  >
    <slot />
  </Primitive>
</template>
