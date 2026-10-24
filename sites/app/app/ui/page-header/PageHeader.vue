<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
  title: string;
  description?: string;
  bordered?: boolean;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  bordered: true,
});
</script>

<template>
  <header
    :class="cn('flex flex-col gap-5', bordered && 'border-b border-border pb-5', props.class)"
  >
    <div
      v-if="$slots.banner"
      class="h-32 overflow-hidden rounded-xl bg-muted md:h-44 [&>img]:size-full [&>img]:object-cover"
    >
      <slot name="banner" />
    </div>

    <slot name="breadcrumb" />

    <div
      :class="
        cn(
          'flex flex-col gap-4 md:flex-row md:justify-between',
          $slots.banner ? 'md:items-end' : 'md:items-start',
        )
      "
    >
      <div
        :class="
          cn(
            'flex min-w-0 flex-col gap-4 sm:flex-row',
            $slots.banner ? 'sm:items-end' : 'sm:items-center',
          )
        "
      >
        <div
          v-if="$slots.avatar"
          :class="
            cn('shrink-0', $slots.banner && '-mt-16 w-fit rounded-full bg-background p-1 md:-mt-20')
          "
        >
          <slot name="avatar" />
        </div>
        <div class="flex min-w-0 flex-col gap-1">
          <h1 class="text-heading-4 font-semibold text-foreground md:text-heading-3">
            {{ title }}
          </h1>
          <p v-if="description" class="text-body-md text-muted-foreground">
            {{ description }}
          </p>
          <div
            v-if="$slots.meta"
            class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-2 text-body-sm text-muted-foreground [&_svg]:size-4"
          >
            <slot name="meta" />
          </div>
        </div>
      </div>

      <div v-if="$slots.actions" class="flex shrink-0 flex-wrap items-center gap-3">
        <slot name="actions" />
      </div>
    </div>

    <slot />
  </header>
</template>
