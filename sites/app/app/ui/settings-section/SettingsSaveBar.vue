<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import { Button } from '@/ui/button';
import { useFuseLocale } from '@/lib/locale';

interface Props {
  show?: boolean;
  processing?: boolean;
  message?: string;
  discardLabel?: string;
  saveLabel?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  show: false,
  processing: false,
  message: () => useFuseLocale().settingsSaveBar.message,
  discardLabel: () => useFuseLocale().settingsSaveBar.discardLabel,
  saveLabel: () => useFuseLocale().settingsSaveBar.saveLabel,
});

defineEmits<{ discard: []; save: [] }>();
</script>

<template>
  <Transition enter-active-class="animate-overlay-in" leave-active-class="animate-overlay-out">
    <div
      v-if="show"
      role="region"
      :aria-label="message"
      :class="
        cn(
          'sticky bottom-4 z-40 mx-auto flex w-full max-w-2xl flex-col gap-3 rounded-xl border border-border bg-popover p-3 pl-4 text-popover-foreground shadow-l sm:flex-row sm:items-center sm:justify-between',
          props.class,
        )
      "
    >
      <p class="text-body-sm font-medium">{{ message }}</p>
      <div class="flex items-center gap-2">
        <Button
          variant="outline"
          size="sm"
          class="flex-1 sm:flex-none"
          :disabled="processing"
          @click="$emit('discard')"
        >
          {{ discardLabel }}
        </Button>
        <Button size="sm" class="flex-1 sm:flex-none" :loading="processing" @click="$emit('save')">
          {{ saveLabel }}
        </Button>
      </div>
    </div>
  </Transition>
</template>
