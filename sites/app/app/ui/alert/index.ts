import { cva, type VariantProps } from 'class-variance-authority';

export { default as Alert } from './Alert.vue';

export const alertVariants = cva(
  'relative flex items-center gap-2.5 rounded-lg px-3.5 py-2.5 text-body-sm transition-colors [&_svg]:size-4.5 [&_svg]:shrink-0',
  {
    variants: {
      variant: {
        solid: '',
        soft: '',
      },
      intent: {
        info: '',
        success: '',
        warning: '',
        danger: '',
      },
    },
    compoundVariants: [
      {
        variant: 'solid',
        intent: 'info',
        class: 'bg-info text-info-foreground',
      },
      {
        variant: 'solid',
        intent: 'success',
        class: 'bg-success text-success-foreground',
      },
      {
        variant: 'solid',
        intent: 'warning',
        class: 'bg-warning text-warning-foreground',
      },
      {
        variant: 'solid',
        intent: 'danger',
        class: 'bg-danger text-danger-foreground',
      },
      {
        variant: 'soft',
        intent: 'info',
        class: 'bg-info-soft text-info-soft-foreground dark:bg-info dark:text-info-foreground',
      },
      {
        variant: 'soft',
        intent: 'success',
        class:
          'bg-success-soft text-success-soft-foreground dark:bg-success dark:text-success-foreground',
      },
      {
        variant: 'soft',
        intent: 'warning',
        class:
          'bg-warning-soft text-warning-soft-foreground dark:bg-warning dark:text-warning-foreground',
      },
      {
        variant: 'soft',
        intent: 'danger',
        class:
          'bg-danger-soft text-danger-soft-foreground dark:bg-danger dark:text-danger-foreground',
      },
    ],
    defaultVariants: {
      variant: 'soft',
      intent: 'info',
    },
  },
);

export type AlertVariants = VariantProps<typeof alertVariants>;
