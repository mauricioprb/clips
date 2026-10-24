<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import {
  ToastProvider,
  ToastRoot,
  ToastTitle,
  ToastDescription,
  ToastAction,
  ToastClose,
  ToastViewport,
} from 'reka-ui';
import { CircleCheck, CircleX, Info, TriangleAlert, X } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { useMediaQuery } from '@/composables/useMediaQuery';
import { alertVariants } from '@/ui/alert';
import { toastVariants, toastViewportVariants, type ToastViewportVariants } from '.';
import { useToast, type ToastItem, type ToastIntent } from './use-toast';
import { useFuseLocale } from '@/lib/locale';

const EXIT_ANIMATION_MS = 200;

interface Props {
  position?: ToastViewportVariants['position'];
  closeLabel?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  position: 'bottom-right',
  closeLabel: () => useFuseLocale().toaster.closeLabel,
});

const { toasts, remove } = useToast();

const prefersReducedMotion = useMediaQuery('(prefers-reduced-motion: reduce)');

const icons = {
  info: Info,
  success: CircleCheck,
  warning: TriangleAlert,
  danger: CircleX,
} satisfies Record<ToastIntent, unknown>;

const swipeDirection = computed<'up' | 'down' | 'left' | 'right'>(() => {
  const position = props.position ?? 'bottom-right';
  if (position.endsWith('right')) return 'right';
  if (position.endsWith('left')) return 'left';
  return position.startsWith('top') ? 'up' : 'down';
});

function onOpenChange(item: ToastItem, open: boolean) {
  item.open = open;
  if (!open) {
    const delay = prefersReducedMotion.value ? 0 : EXIT_ANIMATION_MS;
    setTimeout(() => remove(item.id), delay);
  }
}
</script>

<template>
  <ToastProvider :swipe-direction="swipeDirection">
    <ToastRoot
      v-for="item in toasts"
      :key="item.id"
      :open="item.open"
      :duration="item.duration"
      :class="
        cn(
          alertVariants({
            variant: item.variant ?? 'soft',
            intent: item.intent ?? 'info',
          }),
          toastVariants(),
          props.class,
        )
      "
      @update:open="(open) => onOpenChange(item, open)"
    >
      <component :is="icons[item.intent ?? 'info']" class="mt-0.5" />

      <div class="flex min-w-0 flex-1 flex-col gap-0.5">
        <ToastTitle v-if="item.title" class="font-semibold">
          {{ item.title }}
        </ToastTitle>
        <ToastDescription v-if="item.description" class="opacity-80">
          {{ item.description }}
        </ToastDescription>
      </div>

      <ToastAction v-if="item.action" :alt-text="item.action.label" as-child>
        <button
          type="button"
          class="shrink-0 cursor-pointer rounded-md font-semibold underline-offset-4 outline-none transition-opacity hover:underline focus-visible:underline"
          @click="item.action.onClick()"
        >
          {{ item.action.label }}
        </button>
      </ToastAction>

      <ToastClose
        :aria-label="closeLabel"
        class="shrink-0 cursor-pointer rounded-sm opacity-70 transition-opacity hover:opacity-100 focus-visible:opacity-100 focus-visible:outline-none [&_svg]:size-4"
      >
        <X />
      </ToastClose>
    </ToastRoot>

    <ToastViewport :class="toastViewportVariants({ position })" />
  </ToastProvider>
</template>
