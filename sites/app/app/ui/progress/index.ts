import { cva, type VariantProps } from 'class-variance-authority';

export { default as Progress } from './Progress.vue';

export const progressVariants = cva('relative w-full overflow-hidden rounded-full bg-input', {
  variants: {
    size: {
      sm: 'h-1',
      md: 'h-2',
      lg: 'h-3',
    },
  },
  defaultVariants: {
    size: 'md',
  },
});

export type ProgressVariants = VariantProps<typeof progressVariants>;

export const progressIndicatorVariants = cva(
  'h-full w-full rounded-full transition-transform duration-300 ease-out-quart',
  {
    variants: {
      intent: {
        primary: 'bg-primary',
        success: 'bg-success',
        warning: 'bg-warning',
        danger: 'bg-danger',
      },
    },
    defaultVariants: {
      intent: 'primary',
    },
  },
);

export type ProgressIndicatorVariants = VariantProps<typeof progressIndicatorVariants>;
