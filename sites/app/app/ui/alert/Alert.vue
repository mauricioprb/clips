<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { Primitive, type PrimitiveProps } from 'reka-ui';
import { CircleCheck, CircleX, Info, TriangleAlert, X } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { alertVariants, type AlertVariants } from '.';
import { useFuseLocale } from '@/lib/locale';

interface Props extends PrimitiveProps {
  variant?: AlertVariants['variant'];
  intent?: AlertVariants['intent'];
  title?: string;
  dismissible?: boolean;
  dismissLabel?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  as: 'div',
  dismissLabel: () => useFuseLocale().alert.dismissLabel,
});

defineEmits<{ close: [] }>();

const icons = {
  info: Info,
  success: CircleCheck,
  warning: TriangleAlert,
  danger: CircleX,
};

const currentIcon = computed(() => icons[props.intent ?? 'info']);
</script>

<template>
  <Primitive
    :as="as"
    :as-child="asChild"
    role="alert"
    :class="cn(alertVariants({ variant, intent }), props.class)"
  >
    <slot name="icon">
      <component :is="currentIcon" />
    </slot>

    <div class="min-w-0 flex-1">
      <strong v-if="title" class="font-semibold">{{ title }}</strong>
      {{ ' ' }}
      <slot />
    </div>

    <div v-if="$slots.action" class="shrink-0 font-semibold">
      <slot name="action" />
    </div>

    <button
      v-if="dismissible"
      type="button"
      :aria-label="dismissLabel"
      class="shrink-0 cursor-pointer rounded-sm opacity-70 transition-opacity hover:opacity-100 focus-visible:opacity-100 focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none"
      @click="$emit('close')"
    >
      <X />
    </button>
  </Primitive>
</template>
