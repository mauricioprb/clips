import { cva, type VariantProps } from 'class-variance-authority';

export { default as ScrollArea } from './ScrollArea.vue';
export { default as ScrollBar } from './ScrollBar.vue';

export const scrollBarVariants = cva(
  'z-30 flex touch-none select-none p-px transition-colors duration-150 ease-out-quart',
  {
    variants: {
      orientation: {
        vertical: 'h-full w-1.5 border-l border-l-transparent',
        horizontal: 'h-1.5 flex-col border-t border-t-transparent',
      },
    },
    defaultVariants: {
      orientation: 'vertical',
    },
  },
);

export type ScrollBarVariants = VariantProps<typeof scrollBarVariants>;
