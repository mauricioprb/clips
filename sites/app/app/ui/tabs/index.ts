import { cva, type VariantProps } from 'class-variance-authority';

export { TabsRoot as Tabs } from 'reka-ui';

export { default as TabsList } from './TabsList.vue';
export { default as TabsTrigger } from './TabsTrigger.vue';
export { default as TabsContent } from './TabsContent.vue';

export const tabsListVariants = cva(
  'inline-flex max-w-full items-center overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden',
  {
    variants: {
      variant: {
        underline: 'gap-1 border-b border-border',
        fill: 'gap-1 rounded-lg bg-muted p-1',
        link: 'gap-1',
      },
    },
    defaultVariants: {
      variant: 'underline',
    },
  },
);

export const tabsTriggerVariants = cva(
  'relative inline-flex shrink-0 items-center justify-center gap-2 font-medium whitespace-nowrap cursor-pointer outline-none transition-colors select-none text-muted-foreground hover:text-foreground focus-visible:z-10 focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50 [&_svg]:shrink-0',
  {
    variants: {
      variant: {
        underline:
          'rounded-t-md hover:bg-accent after:absolute after:inset-x-0 after:-bottom-px after:h-0.5 after:rounded-full after:bg-transparent data-[state=active]:text-foreground data-[state=active]:after:bg-primary',
        fill: 'rounded-md hover:bg-background/60 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-xs data-[state=active]:hover:bg-background',
        link: 'rounded-md hover:bg-accent data-[state=active]:text-primary-text',
      },
      size: {
        lg: 'h-11 px-4 text-action-lg [&_svg]:size-5',
        md: 'h-10 px-3.5 text-action-md [&_svg]:size-4',
        sm: 'h-8 px-3 text-action-sm [&_svg]:size-3.5',
      },
    },
    defaultVariants: {
      variant: 'underline',
      size: 'md',
    },
  },
);

export type TabsListVariants = VariantProps<typeof tabsListVariants>;
export type TabsTriggerVariants = VariantProps<typeof tabsTriggerVariants>;
