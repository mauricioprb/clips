<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { Check, CircleX } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { fieldMessageVariants, type FieldMessageVariants } from '.';

interface Props {
  id?: string;
  status?: FieldMessageVariants['status'];
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  status: 'default',
});
</script>

<template>
  <p
    :id="id"
    :aria-live="status === 'error' ? 'assertive' : 'polite'"
    :class="cn(fieldMessageVariants({ status }), props.class)"
  >
    <Check v-if="status === 'success'" class="size-3.5 shrink-0" />
    <CircleX v-else-if="status === 'error'" class="size-3.5 shrink-0" />
    <slot />
  </p>
</template>
