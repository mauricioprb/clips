import { cva, type VariantProps } from 'class-variance-authority';

export { default as Switch } from './Switch.vue';

export const switchTrackVariants = cva(
  'inline-flex shrink-0 cursor-pointer items-center rounded-full border border-transparent bg-input transition-colors duration-150 ease-out-quart focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:ring-offset-2 focus-visible:ring-offset-background focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary',
  {
    variants: {
      size: {
        sm: 'h-4 w-7',
        md: 'h-5 w-9',
      },
    },
    defaultVariants: {
      size: 'md',
    },
  },
);

export const switchThumbVariants = cva(
  'pointer-events-none block translate-x-0.5 rounded-full bg-muted-foreground shadow-xs transition-[transform,background-color] data-[state=checked]:bg-primary-foreground dark:data-[state=unchecked]:bg-foreground duration-150 ease-out-quart',
  {
    variants: {
      size: {
        sm: 'size-3 data-[state=checked]:translate-x-3.5',
        md: 'size-4 data-[state=checked]:translate-x-4',
      },
    },
    defaultVariants: {
      size: 'md',
    },
  },
);

export type SwitchVariants = VariantProps<typeof switchTrackVariants>;
