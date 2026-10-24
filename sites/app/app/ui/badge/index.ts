import { cva, type VariantProps } from 'class-variance-authority';

export { default as Badge } from './Badge.vue';

export const badgeVariants = cva(
  'inline-flex items-center font-medium leading-none transition-colors [&_svg]:shrink-0',
  {
    variants: {
      variant: {
        solid: 'ring-1 ring-inset ring-transparent',
        soft: 'ring-1 ring-inset',
        modern: 'bg-card text-foreground ring-1 ring-inset ring-border shadow-xs',
      },
      intent: {
        primary: '',
        neutral: '',
        success: '',
        warning: '',
        danger: '',
        blue: '',
        sky: '',
        cyan: '',
        teal: '',
        green: '',
        yellow: '',
        amber: '',
        orange: '',
        red: '',
        violet: '',
        purple: '',
        indigo: '',
      },
      shape: {
        pill: 'rounded-full',
        badge: 'rounded-md',
      },
      size: {
        lg: 'h-7.5 gap-1.5 px-3 text-body-sm [&_svg]:size-4',
        md: 'h-5 gap-1.5 px-2.5 text-caption [&_svg]:size-3.5',
        sm: 'h-4.5 gap-1 px-2 text-[0.6875rem] [&_svg]:size-3',
      },
    },
    compoundVariants: [
      {
        variant: 'solid',
        intent: 'primary',
        class: 'bg-primary text-primary-foreground',
      },
      {
        variant: 'solid',
        intent: 'neutral',
        class: 'bg-foreground text-background',
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
        variant: 'solid',
        intent: 'blue',
        class: 'bg-blue-spark-700 text-white',
      },
      {
        variant: 'solid',
        intent: 'sky',
        class: 'bg-sea-sight-700 text-white',
      },
      {
        variant: 'solid',
        intent: 'cyan',
        class: 'bg-macquarie-700 text-white',
      },
      {
        variant: 'solid',
        intent: 'teal',
        class: 'bg-generic-viridian-700 text-white',
      },
      {
        variant: 'solid',
        intent: 'green',
        class: 'bg-green-bell-pepper-700 text-white',
      },
      {
        variant: 'solid',
        intent: 'yellow',
        class: 'bg-sizzling-sunrise-700 text-white',
      },
      {
        variant: 'solid',
        intent: 'amber',
        class: 'bg-golden-stone-700 text-white',
      },
      {
        variant: 'solid',
        intent: 'orange',
        class: 'bg-lucky-orange-700 text-white',
      },
      {
        variant: 'solid',
        intent: 'red',
        class: 'bg-electric-crimson-700 text-white',
      },
      {
        variant: 'solid',
        intent: 'violet',
        class: 'bg-snappy-violet-700 text-white',
      },
      {
        variant: 'solid',
        intent: 'purple',
        class: 'bg-psychedelic-purple-700 text-white',
      },
      {
        variant: 'solid',
        intent: 'indigo',
        class: 'bg-electric-indigo-700 text-white',
      },

      {
        variant: 'soft',
        intent: 'primary',
        class: 'bg-primary-soft text-primary-soft-foreground ring-primary/20',
      },
      {
        variant: 'soft',
        intent: 'neutral',
        class: 'bg-muted text-muted-foreground ring-border',
      },
      {
        variant: 'soft',
        intent: 'success',
        class: 'bg-success-soft text-success-soft-foreground ring-success/20',
      },
      {
        variant: 'soft',
        intent: 'warning',
        class: 'bg-warning-soft text-warning-soft-foreground ring-warning/20',
      },
      {
        variant: 'soft',
        intent: 'danger',
        class: 'bg-danger-soft text-danger-soft-foreground ring-danger/20',
      },
      {
        variant: 'soft',
        intent: 'blue',
        class:
          'bg-blue-spark-100 text-blue-spark-700 ring-blue-spark-700/20 dark:bg-blue-spark-900/40 dark:text-blue-spark-200 dark:ring-blue-spark-300/25',
      },
      {
        variant: 'soft',
        intent: 'sky',
        class:
          'bg-sea-sight-100 text-sea-sight-700 ring-sea-sight-700/20 dark:bg-sea-sight-900/40 dark:text-sea-sight-200 dark:ring-sea-sight-300/25',
      },
      {
        variant: 'soft',
        intent: 'cyan',
        class:
          'bg-macquarie-100 text-macquarie-700 ring-macquarie-700/20 dark:bg-macquarie-900/40 dark:text-macquarie-200 dark:ring-macquarie-300/25',
      },
      {
        variant: 'soft',
        intent: 'teal',
        class:
          'bg-generic-viridian-100 text-generic-viridian-700 ring-generic-viridian-700/20 dark:bg-generic-viridian-900/40 dark:text-generic-viridian-200 dark:ring-generic-viridian-300/25',
      },
      {
        variant: 'soft',
        intent: 'green',
        class:
          'bg-green-bell-pepper-100 text-green-bell-pepper-700 ring-green-bell-pepper-700/20 dark:bg-green-bell-pepper-900/40 dark:text-green-bell-pepper-200 dark:ring-green-bell-pepper-300/25',
      },
      {
        variant: 'soft',
        intent: 'yellow',
        class:
          'bg-sizzling-sunrise-100 text-sizzling-sunrise-700 ring-sizzling-sunrise-700/20 dark:bg-sizzling-sunrise-900/40 dark:text-sizzling-sunrise-200 dark:ring-sizzling-sunrise-300/25',
      },
      {
        variant: 'soft',
        intent: 'amber',
        class:
          'bg-golden-stone-100 text-golden-stone-700 ring-golden-stone-700/20 dark:bg-golden-stone-900/40 dark:text-golden-stone-200 dark:ring-golden-stone-300/25',
      },
      {
        variant: 'soft',
        intent: 'orange',
        class:
          'bg-lucky-orange-100 text-lucky-orange-700 ring-lucky-orange-700/20 dark:bg-lucky-orange-900/40 dark:text-lucky-orange-200 dark:ring-lucky-orange-300/25',
      },
      {
        variant: 'soft',
        intent: 'red',
        class:
          'bg-electric-crimson-100 text-electric-crimson-700 ring-electric-crimson-700/20 dark:bg-electric-crimson-900/40 dark:text-electric-crimson-200 dark:ring-electric-crimson-300/25',
      },
      {
        variant: 'soft',
        intent: 'violet',
        class:
          'bg-snappy-violet-100 text-snappy-violet-700 ring-snappy-violet-700/20 dark:bg-snappy-violet-900/40 dark:text-snappy-violet-200 dark:ring-snappy-violet-300/25',
      },
      {
        variant: 'soft',
        intent: 'purple',
        class:
          'bg-psychedelic-purple-100 text-psychedelic-purple-700 ring-psychedelic-purple-700/20 dark:bg-psychedelic-purple-900/40 dark:text-psychedelic-purple-200 dark:ring-psychedelic-purple-300/25',
      },
      {
        variant: 'soft',
        intent: 'indigo',
        class:
          'bg-electric-indigo-100 text-electric-indigo-700 ring-electric-indigo-700/20 dark:bg-electric-indigo-900/40 dark:text-electric-indigo-200 dark:ring-electric-indigo-300/25',
      },
    ],
    defaultVariants: {
      variant: 'soft',
      intent: 'primary',
      shape: 'badge',
      size: 'md',
    },
  },
);

export type BadgeVariants = VariantProps<typeof badgeVariants>;
