import { cva, type VariantProps } from 'class-variance-authority';

export { TooltipProvider, TooltipRoot as Tooltip, TooltipTrigger } from 'reka-ui';

export { default as TooltipContent } from './TooltipContent.vue';

export const tooltipVariants = cva(
  'relative z-50 flex max-w-xs items-start gap-2.5 rounded-lg px-3.5 py-3 text-body-sm shadow-l origin-(--reka-tooltip-content-transform-origin) data-[state=instant-open]:animate-overlay-in data-[state=delayed-open]:animate-overlay-in data-[state=closed]:animate-overlay-out',
  {
    variants: {
      variant: {
        dark: 'bg-foreground text-background dark:border dark:border-border dark:bg-popover dark:text-popover-foreground',
        light:
          'border border-border bg-popover text-popover-foreground dark:border-transparent dark:bg-foreground dark:text-background',
      },
    },
    defaultVariants: {
      variant: 'dark',
    },
  },
);

export type TooltipVariants = VariantProps<typeof tooltipVariants>;
