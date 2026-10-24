import { cva, type VariantProps } from 'class-variance-authority';

export { default as FieldMessage } from './FieldMessage.vue';

export const fieldMessageVariants = cva('flex items-center gap-1 text-caption', {
  variants: {
    status: {
      default: 'text-muted-foreground',
      success: 'text-success-soft-foreground',
      error: 'text-danger-soft-foreground',
    },
  },
  defaultVariants: {
    status: 'default',
  },
});

export type FieldMessageVariants = VariantProps<typeof fieldMessageVariants>;
