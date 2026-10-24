import { cva, type VariantProps } from 'class-variance-authority';

export { default as Avatar } from './Avatar.vue';

export const avatarVariants = cva(
  'relative inline-flex shrink-0 items-center justify-center overflow-hidden align-middle',
  {
    variants: {
      shape: {
        circle: 'rounded-full',
        rounded: 'rounded-lg',
      },
      size: {
        xs: 'size-6 text-[0.5625rem]',
        sm: 'size-8 text-caption',
        md: 'size-10 text-body-sm',
        lg: 'size-12 text-body-md',
        xl: 'size-14 text-body-lg',
      },
    },
    defaultVariants: {
      shape: 'circle',
      size: 'md',
    },
  },
);

export type AvatarVariants = VariantProps<typeof avatarVariants>;
