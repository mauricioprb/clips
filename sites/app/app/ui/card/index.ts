import { cva, type VariantProps } from 'class-variance-authority';

export { default as Card } from './Card.vue';
export { default as CardImage } from './CardImage.vue';
export { default as CardHeader } from './CardHeader.vue';
export { default as CardTitle } from './CardTitle.vue';
export { default as CardDescription } from './CardDescription.vue';
export { default as CardContent } from './CardContent.vue';
export { default as CardFooter } from './CardFooter.vue';

export const cardVariants = cva(
  'group/card relative isolate flex flex-col overflow-hidden rounded-xl',
  {
    variants: {
      variant: {
        default: 'border border-border bg-card text-card-foreground shadow-s',
        overlay: 'bg-card text-white shadow-s',
        inverted: 'bg-foreground text-background shadow-s',
        ghost: 'bg-transparent',
      },
      hover: {
        true: 'transition-colors hover:border-primary-text hover:bg-primary/5 cursor-pointer',
        false: '',
      },
    },
    defaultVariants: {
      variant: 'default',
      hover: false,
    },
  },
);

export type CardVariants = VariantProps<typeof cardVariants>;
