import { cva, type VariantProps } from 'class-variance-authority';

export { default as Toggle } from './Toggle.vue';
export { default as ToggleGroup } from './ToggleGroup.vue';
export { default as ToggleGroupItem } from './ToggleGroupItem.vue';

export const toggleVariants = cva(
  'inline-flex items-center justify-center gap-2 rounded-md font-medium text-foreground outline-none cursor-pointer transition-colors hover:bg-foreground/10 active:bg-foreground/20 focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:ring-offset-1 focus-visible:ring-offset-background disabled:pointer-events-none disabled:opacity-50 data-[state=on]:bg-accent data-[state=on]:text-accent-foreground [&_svg]:shrink-0',
  {
    variants: {
      variant: {
        default: 'bg-transparent',
        outline: 'border border-border bg-transparent hover:border-foreground/30',
        segmented:
          'rounded-md text-muted-foreground hover:bg-transparent active:bg-transparent hover:text-foreground data-[state=on]:bg-background data-[state=on]:text-foreground data-[state=on]:shadow-xs',
      },
      size: {
        sm: 'h-8 min-w-8 px-2 text-action-md [&_svg]:size-4',
        md: 'h-9 min-w-9 px-2.5 text-action-md [&_svg]:size-4',
        lg: 'h-10 min-w-10 px-3 text-action-lg [&_svg]:size-5',
      },
    },
    compoundVariants: [
      { variant: 'segmented', size: 'sm', class: 'px-3' },
      { variant: 'segmented', size: 'md', class: 'px-3.5' },
      { variant: 'segmented', size: 'lg', class: 'px-4' },
    ],
    defaultVariants: {
      variant: 'default',
      size: 'md',
    },
  },
);

export type ToggleVariants = VariantProps<typeof toggleVariants>;

export const toggleGroupVariants = cva('inline-flex items-center', {
  variants: {
    variant: {
      default: 'gap-1',
      outline: 'gap-1',
      segmented: 'gap-1 rounded-lg bg-muted p-1',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

export type ToggleGroupVariants = VariantProps<typeof toggleGroupVariants>;
