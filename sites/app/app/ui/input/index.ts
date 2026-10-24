import { cva, type VariantProps } from 'class-variance-authority';

export { default as Input } from './Input.vue';

export const inputWrapperVariants = cva(
  'flex items-stretch overflow-hidden rounded-lg border bg-background text-body-sm transition-colors',
  {
    variants: {
      status: {
        default:
          'border-input text-foreground focus-within:border-ring focus-within:ring-2 focus-within:ring-ring/30',
        success:
          'border-success bg-success-soft text-success-soft-foreground focus-within:ring-2 focus-within:ring-success/30',
        error:
          'border-danger bg-danger-soft text-danger-soft-foreground focus-within:ring-2 focus-within:ring-danger/30',
      },
    },
    defaultVariants: {
      status: 'default',
    },
  },
);

export type InputWrapperVariants = VariantProps<typeof inputWrapperVariants>;
