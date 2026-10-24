<script setup lang="ts">
import type { Component, HTMLAttributes } from 'vue';
import { Primitive, type PrimitiveProps } from 'reka-ui';
import { X } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { badgeVariants, type BadgeVariants } from '.';
import { useFuseLocale } from '@/lib/locale';

interface Props extends PrimitiveProps {
  variant?: BadgeVariants['variant'];
  intent?: BadgeVariants['intent'];
  shape?: BadgeVariants['shape'];
  size?: BadgeVariants['size'];
  dot?: boolean;
  iconLeading?: Component;
  iconTrailing?: Component;
  dismissible?: boolean;
  dismissLabel?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  as: 'span',
  intent: 'primary',
  size: 'md',
  dismissLabel: () => useFuseLocale().badge.dismissLabel,
});

defineEmits<{ close: [] }>();
</script>

<template>
  <Primitive
    :as="as"
    :as-child="asChild"
    :class="cn(badgeVariants({ variant, intent, shape, size }), props.class)"
  >
    <span v-if="dot" class="size-1.5 shrink-0 rounded-full bg-current" aria-hidden="true" />
    <slot name="icon"><component :is="iconLeading" v-if="iconLeading" /></slot>
    <span v-if="$slots.default" class="[text-box:trim-both_cap_alphabetic]">
      <slot />
    </span>
    <component :is="iconTrailing" v-if="iconTrailing" />
    <button
      v-if="dismissible"
      type="button"
      :aria-label="dismissLabel"
      class="-mr-0.5 cursor-pointer rounded-sm opacity-70 transition-opacity hover:opacity-100 focus-visible:opacity-100 focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none"
      @click="$emit('close')"
    >
      <X />
    </button>
  </Primitive>
</template>
