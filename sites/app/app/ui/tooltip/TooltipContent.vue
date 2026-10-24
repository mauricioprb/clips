<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import {
  TooltipContent,
  TooltipPortal,
  injectTooltipRootContext,
  type TooltipContentProps,
} from 'reka-ui';
import { Info, X } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { tooltipVariants, type TooltipVariants } from '.';
import { useFuseLocale } from '@/lib/locale';

interface Props {
  variant?: TooltipVariants['variant'];
  side?: TooltipContentProps['side'];
  sideOffset?: number;
  title?: string;
  description?: string;
  icon?: boolean;
  dismissible?: boolean;
  dismissLabel?: string;
  arrow?: boolean;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'dark',
  sideOffset: 8,
  icon: true,
  dismissible: false,
  dismissLabel: () => useFuseLocale().tooltipContent.dismissLabel,
  arrow: true,
});

defineOptions({ inheritAttrs: false });

const rootContext = injectTooltipRootContext();

const isRich = computed(() => Boolean(props.title || props.description));

const arrowClass = computed(() =>
  cn(
    'pointer-events-none absolute size-2.5 rotate-45 rounded-[2px] border-transparent',
    props.variant === 'light'
      ? 'border-border bg-popover dark:border-transparent dark:bg-foreground'
      : 'bg-foreground dark:border-border dark:bg-popover',
    'group-data-[side=top]/tt:bottom-0 group-data-[side=top]/tt:left-1/2 group-data-[side=top]/tt:-translate-x-1/2 group-data-[side=top]/tt:translate-y-1/2 group-data-[side=top]/tt:border-r group-data-[side=top]/tt:border-b',
    'group-data-[side=bottom]/tt:top-0 group-data-[side=bottom]/tt:left-1/2 group-data-[side=bottom]/tt:-translate-x-1/2 group-data-[side=bottom]/tt:-translate-y-1/2 group-data-[side=bottom]/tt:border-t group-data-[side=bottom]/tt:border-l',
    'group-data-[side=left]/tt:right-0 group-data-[side=left]/tt:top-1/2 group-data-[side=left]/tt:-translate-y-1/2 group-data-[side=left]/tt:translate-x-1/2 group-data-[side=left]/tt:border-t group-data-[side=left]/tt:border-r',
    'group-data-[side=right]/tt:left-0 group-data-[side=right]/tt:top-1/2 group-data-[side=right]/tt:-translate-y-1/2 group-data-[side=right]/tt:-translate-x-1/2 group-data-[side=right]/tt:border-b group-data-[side=right]/tt:border-l',
  ),
);
</script>

<template>
  <TooltipPortal>
    <TooltipContent
      v-bind="$attrs"
      :side="side"
      :side-offset="sideOffset"
      :class="cn('group/tt', tooltipVariants({ variant }), props.class)"
    >
      <span v-if="isRich && icon" class="mt-px shrink-0 opacity-80 [&_svg]:size-4">
        <slot name="icon">
          <Info />
        </slot>
      </span>

      <div v-if="isRich" class="flex min-w-0 flex-1 flex-col gap-0.5">
        <strong v-if="title" class="font-semibold">{{ title }}</strong>
        <span v-if="description" class="opacity-70">{{ description }}</span>
        <slot />
      </div>
      <slot v-else />

      <button
        v-if="dismissible"
        type="button"
        :aria-label="dismissLabel"
        class="-mr-1 mt-px shrink-0 cursor-pointer rounded-sm opacity-70 transition-opacity hover:opacity-100 focus-visible:opacity-100 focus-visible:outline-none [&_svg]:size-4"
        @click="rootContext.onClose()"
      >
        <X />
      </button>

      <span v-if="arrow" :class="arrowClass" />
    </TooltipContent>
  </TooltipPortal>
</template>
