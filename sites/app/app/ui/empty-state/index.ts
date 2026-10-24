import { cva, type VariantProps } from 'class-variance-authority';

export { default as EmptyState } from './EmptyState.vue';

export const emptyStateVariants = cva('flex flex-col items-center justify-center text-center', {
  variants: {
    size: {
      sm: 'gap-2 px-4 py-8',
      md: 'gap-3 px-6 py-12',
      lg: 'gap-4 px-6 py-16',
    },
    bordered: {
      true: 'rounded-xl border border-dashed border-border',
      false: '',
    },
  },
  defaultVariants: {
    size: 'md',
    bordered: false,
  },
});

export const emptyStateMediaVariants = cva(
  'flex items-center justify-center rounded-full bg-muted text-muted-foreground [&_svg]:shrink-0',
  {
    variants: {
      size: {
        sm: 'size-10 [&_svg]:size-5',
        md: 'size-12 [&_svg]:size-6',
        lg: 'size-14 [&_svg]:size-7',
      },
    },
    defaultVariants: {
      size: 'md',
    },
  },
);

export type EmptyStateVariants = VariantProps<typeof emptyStateVariants>;
