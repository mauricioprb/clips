<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import type { AuthCardLayout } from '.';

interface Props {
  title: string;
  description?: string;
  layout?: AuthCardLayout;
  expressive?: boolean;
  asideTitle?: string;
  asideDescription?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  layout: 'card',
  expressive: false,
});
</script>

<template>
  <div :class="cn('relative flex min-h-svh w-full bg-background', props.class)">
    <div class="absolute top-5 right-5 sm:top-8 sm:right-8"><ThemeToggle /></div>
    <div class="flex flex-1 items-center justify-center px-5 py-24 sm:px-8">
      <div
        :class="
          cn(
            'flex w-full max-w-sm flex-col gap-8',
            layout === 'card' &&
              'max-w-md rounded-xl border border-border bg-card p-6 text-card-foreground sm:p-8',
          )
        "
      >
        <div class="flex flex-col items-center gap-6 text-center">
          <slot name="logo" />
          <div class="flex flex-col gap-2">
            <h1 class="text-heading-4 font-semibold text-foreground">
              {{ title }}
            </h1>
            <p v-if="description" class="text-body-md text-muted-foreground">
              {{ description }}
            </p>
          </div>
        </div>

        <slot />

        <div v-if="$slots.footer" class="text-center text-body-sm text-muted-foreground">
          <slot name="footer" />
        </div>
      </div>
    </div>

    <div
      v-if="layout === 'split' && $slots.aside"
      class="relative hidden flex-1 overflow-hidden bg-muted lg:block"
    >
      <slot name="aside" />
    </div>
  </div>
</template>
