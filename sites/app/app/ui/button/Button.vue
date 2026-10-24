<script setup lang="ts">
import type { Component, HTMLAttributes } from 'vue';
import { Primitive, type PrimitiveProps } from 'reka-ui';
import { Loader2 } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { buttonVariants, type ButtonVariants } from '.';

interface Props extends PrimitiveProps {
  variant?: ButtonVariants['variant'];
  tone?: ButtonVariants['tone'];
  size?: ButtonVariants['size'];
  iconOnly?: boolean;
  iconLeading?: Component;
  iconTrailing?: Component;
  loading?: boolean;
  loadingText?: string;
  disabled?: boolean;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  as: 'button',
});
</script>

<template>
  <Primitive
    :as="as"
    :as-child="asChild"
    :disabled="disabled || loading || undefined"
    :aria-busy="loading || undefined"
    :data-loading="loading || undefined"
    :class="cn(buttonVariants({ variant, tone, size, iconOnly }), props.class)"
  >
    <template v-if="!asChild">
      <Loader2 v-if="loading" class="animate-spin" />
      <component :is="iconLeading" v-else-if="iconLeading" />
      <span v-if="loading && loadingText">{{ loadingText }}</span>
      <slot v-else />
      <component :is="iconTrailing" v-if="iconTrailing && !loading" />
    </template>
    <slot v-else />
  </Primitive>
</template>
