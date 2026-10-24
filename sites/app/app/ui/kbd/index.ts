import { cva, type VariantProps } from 'class-variance-authority';

export { default as Kbd } from './Kbd.vue';

export const kbdVariants = cva(
  'pointer-events-none inline-flex shrink-0 select-none items-center justify-center gap-0.5 rounded border border-border bg-muted font-sans font-medium text-muted-foreground [&_svg]:shrink-0',
  {
    variants: {
      size: {
        sm: 'h-4.5 min-w-4.5 px-1 text-[0.625rem] [&_svg]:size-2.5',
        md: 'h-5 min-w-5 px-1.5 text-caption [&_svg]:size-3',
      },
    },
    defaultVariants: {
      size: 'md',
    },
  },
);

export type KbdVariants = VariantProps<typeof kbdVariants>;
