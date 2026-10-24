<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
  title: string;
  description?: string;
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
  <section
    :class="cn('grid gap-5 lg:grid-cols-[minmax(0,18rem)_minmax(0,1fr)] lg:gap-10', props.class)"
  >
    <div class="flex flex-col gap-1">
      <h2 class="text-heading-6 font-semibold text-foreground">{{ title }}</h2>
      <p v-if="description" class="text-body-sm text-muted-foreground">
        {{ description }}
      </p>
    </div>
    <div
      class="flex min-w-0 flex-col overflow-hidden rounded-xl border border-border bg-card text-card-foreground shadow-xs"
    >
      <div class="flex flex-col divide-y divide-border">
        <slot />
      </div>
      <div
        v-if="$slots.footer"
        class="flex flex-wrap items-center justify-end gap-3 border-t border-border bg-muted/40 px-5 py-3.5 sm:px-6"
      >
        <slot name="footer" />
      </div>
    </div>
  </section>
</template>
