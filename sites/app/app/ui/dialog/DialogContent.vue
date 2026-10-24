<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { DialogClose, DialogContent, DialogOverlay, DialogPortal } from 'reka-ui';
import { X } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { useFuseLocale } from '@/lib/locale';

interface Props {
  align?: 'start' | 'center';
  closeLabel?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  align: 'start',
  closeLabel: () => useFuseLocale().dialogContent.closeLabel,
});

defineOptions({ inheritAttrs: false });
</script>

<template>
  <DialogPortal>
    <DialogOverlay
      class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs data-[state=open]:animate-fade-in data-[state=closed]:animate-fade-out"
    />
    <DialogContent
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
      <DialogClose
        :aria-label="closeLabel"
        class="absolute top-4 right-4 cursor-pointer rounded-md opacity-70 transition-opacity hover:opacity-100 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
      >
        <X class="size-5" />
      </DialogClose>
    </DialogContent>
  </DialogPortal>
</template>
