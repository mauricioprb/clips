<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { AlertDialogContent, AlertDialogOverlay, AlertDialogPortal } from 'reka-ui';
import { cn } from '@/lib/utils';

interface Props {
  align?: 'start' | 'center';
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  align: 'start',
});

defineOptions({ inheritAttrs: false });
</script>

<template>
  <AlertDialogPortal>
    <AlertDialogOverlay
      class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs data-[state=open]:animate-fade-in data-[state=closed]:animate-fade-out"
    />
    <AlertDialogContent
      v-bind="$attrs"
      :class="
        cn(
          'fixed top-1/2 left-1/2 z-50 grid max-h-[calc(100dvh-2rem)] w-[calc(100%-2rem)] max-w-md origin-center -translate-x-1/2 -translate-y-1/2 gap-3 overflow-y-auto rounded-xl border border-border bg-popover p-5 text-popover-foreground shadow-xl data-[state=open]:animate-overlay-in data-[state=closed]:animate-overlay-out',
          align === 'center' && 'justify-items-center text-center',
          props.class,
        )
      "
    >
      <slot />
    </AlertDialogContent>
  </AlertDialogPortal>
</template>
