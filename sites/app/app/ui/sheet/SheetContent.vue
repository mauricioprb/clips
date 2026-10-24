<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import {
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogOverlay,
  DialogPortal,
  DialogTitle,
} from 'reka-ui';
import { X } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { sheetVariants, type SheetVariants } from '.';
import { useFuseLocale } from '@/lib/locale';

interface Props {
  side?: SheetVariants['side'];
  closeLabel?: string;
  title?: string;
  description?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  side: 'right',
  closeLabel: () => useFuseLocale().sheetContent.closeLabel,
  title: () => useFuseLocale().sheetContent.title,
  description: () => useFuseLocale().sheetContent.description,
});

defineOptions({ inheritAttrs: false });
</script>

<template>
  <DialogPortal>
    <DialogOverlay
      class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs data-[state=open]:animate-fade-in data-[state=closed]:animate-fade-out"
    />
    <DialogContent v-bind="$attrs" :class="cn(sheetVariants({ side }), props.class)">
      <DialogTitle class="sr-only">{{ title }}</DialogTitle>
      <DialogDescription class="sr-only">{{ description }}</DialogDescription>
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
