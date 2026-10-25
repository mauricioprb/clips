<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { ToggleGroupRoot } from 'reka-ui';
import { cn } from '@/lib/utils';
import { toggleGroupVariants, type ToggleVariants } from '.';
import { provideToggleGroupContext } from './context';

interface Props {
  variant?: ToggleVariants['variant'];
  size?: ToggleVariants['size'];
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  size: 'md',
});

defineOptions({ inheritAttrs: false });

provideToggleGroupContext(
  computed(() => ({
    variant: props.variant ?? 'default',
    size: props.size ?? 'md',
  })),
);
</script>

<template>
  <ToggleGroupRoot v-bind="$attrs" :class="cn(toggleGroupVariants({ variant }), props.class)">
    <slot />
  </ToggleGroupRoot>
</template>
