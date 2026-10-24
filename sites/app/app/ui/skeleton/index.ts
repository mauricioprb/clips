import { cva, type VariantProps } from 'class-variance-authority';

export { default as Skeleton } from './Skeleton.vue';

export const skeletonVariants = cva('block animate-pulse bg-muted', {
  variants: {
    variant: {
      default: 'rounded-md',
      text: 'h-4 rounded',
      circle: 'rounded-full',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

export type SkeletonVariants = VariantProps<typeof skeletonVariants>;
