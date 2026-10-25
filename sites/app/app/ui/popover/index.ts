import { cva } from 'class-variance-authority';

export { PopoverRoot as Popover, PopoverTrigger, PopoverAnchor, PopoverClose } from 'reka-ui';

export { default as PopoverContent } from './PopoverContent.vue';

export const popoverContentVariants = cva(
  'z-50 w-72 rounded-xl border border-border bg-popover p-4 text-body-sm text-popover-foreground shadow-l outline-none data-[state=open]:animate-fade-in data-[state=closed]:animate-fade-out',
);
