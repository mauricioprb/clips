<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { Toggle } from 'reka-ui';
import { cn } from '@/lib/utils';
import { toggleVariants, type ToggleVariants } from '.';

interface Props {
  variant?: ToggleVariants['variant'];
  size?: ToggleVariants['size'];
  disabled?: boolean;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  size: 'md',
});

const pressed = defineModel<boolean>();

defineOptions({ inheritAttrs: false });
</script>

<template>
  <Toggle
    v-bind="$attrs"
    v-model="pressed"
    :disabled="disabled"
    :class="cn(toggleVariants({ variant, size }), props.class)"
  >
    <slot />
  </Toggle>
</template>
