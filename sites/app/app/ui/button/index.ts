import { cva, type VariantProps } from 'class-variance-authority';

export { default as Button } from './Button.vue';

export const buttonVariants = cva(
  'inline-flex items-center justify-center gap-2 whitespace-nowrap font-semibold cursor-pointer transition-[color,background-color,border-color] duration-150 ease-out-quart focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:pointer-events-none disabled:opacity-50 [&_svg]:shrink-0',
  {
    variants: {
      variant: {
        primary: '',
        secondary: '',
        outline: 'border bg-transparent',
        ghost: 'bg-transparent',
        link: 'bg-transparent underline-offset-4 hover:underline',
        inverse: '',
      },
      tone: {
        default: '',
        danger: '',
      },
      size: {
        lg: 'h-10 rounded-lg px-5 text-action-lg [&_svg]:size-5',
        md: 'h-9 rounded-lg px-3.5 text-action-md [&_svg]:size-4',
        sm: 'h-8 rounded-md px-3 text-action-md [&_svg]:size-3.5',
        xs: 'h-7 rounded-md px-2.5 text-action-sm [&_svg]:size-3.5',
      },
      iconOnly: {
        true: 'p-0',
        false: '',
      },
    },
    compoundVariants: [
      {
        variant: 'primary',
        tone: 'default',
        class: 'bg-primary text-primary-foreground hover:bg-primary-hover active:bg-primary-active',
      },
      {
        variant: 'primary',
        tone: 'danger',
        class: 'bg-danger text-danger-foreground hover:bg-danger-hover active:bg-danger-active',
      },
      {
        variant: 'secondary',
        tone: 'default',
        class:
          'bg-secondary text-secondary-foreground hover:bg-secondary-hover active:bg-secondary-active',
      },
      {
        variant: 'secondary',
        tone: 'danger',
        class: 'bg-danger-soft text-danger-soft-foreground hover:bg-danger-soft-hover',
      },
      {
        variant: 'outline',
        tone: 'default',
        class:
          'border-border text-foreground hover:border-foreground/30 hover:bg-foreground/10 active:bg-foreground/20',
      },
      {
        variant: 'outline',
        tone: 'danger',
        class: 'border-danger text-danger hover:bg-danger/10 active:bg-danger/20',
      },
      {
        variant: 'ghost',
        tone: 'default',
        class: 'text-foreground hover:bg-foreground/10 active:bg-foreground/20',
      },
      {
        variant: 'ghost',
        tone: 'danger',
        class: 'text-danger hover:bg-danger/10 active:bg-danger/20',
      },
      {
        variant: 'inverse',
        tone: 'default',
        class:
          'bg-primary-foreground text-primary hover:bg-primary-foreground/90 active:bg-primary-foreground/80',
      },
      {
        variant: 'inverse',
        tone: 'danger',
        class:
          'bg-danger-foreground text-danger hover:bg-danger-foreground/90 active:bg-danger-foreground/80',
      },
      { variant: 'link', tone: 'default', class: 'text-primary-text' },
      { variant: 'link', tone: 'danger', class: 'text-danger' },

      { iconOnly: true, size: 'lg', class: 'size-10 px-0' },
      { iconOnly: true, size: 'md', class: 'size-9 px-0' },
      { iconOnly: true, size: 'sm', class: 'size-8 px-0' },
      { iconOnly: true, size: 'xs', class: 'size-7 px-0' },
    ],
    defaultVariants: {
      variant: 'primary',
      tone: 'default',
      size: 'md',
      iconOnly: false,
    },
  },
);

export type ButtonVariants = VariantProps<typeof buttonVariants>;
