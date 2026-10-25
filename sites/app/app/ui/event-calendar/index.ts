import { cva, type VariantProps } from 'class-variance-authority';

export { default as EventChip } from './EventChip.vue';
export { default as EventCalendarMonthView } from './EventCalendarMonthView.vue';

export * from './types';
export * from './utils';

export const eventChipVariants = cva(
  'group/event relative flex w-full cursor-pointer select-none overflow-hidden text-left ring-1 ring-inset transition-colors focus-visible:z-10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/60',
  {
    variants: {
      variant: {
        soft: '',
        solid: 'text-white ring-transparent',
      },
      color: {
        primary: '',
        neutral: '',
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
      block: {
        true: 'h-full flex-col gap-0.5 rounded-lg px-2 py-1.5 text-caption',
        false: 'h-6 items-center gap-1.5 rounded-md px-1.5 text-caption',
      },
    },
    compoundVariants: [
      {
        variant: 'soft',
        color: 'primary',
        class: 'bg-primary-soft text-primary-soft-foreground ring-primary/20',
      },
      {
        variant: 'soft',
        color: 'neutral',
        class: 'bg-muted text-foreground ring-border',
      },
      {
        variant: 'soft',
        color: 'blue',
        class:
          'bg-blue-spark-100 text-blue-spark-700 ring-blue-spark-700/20 dark:bg-blue-spark-900/40 dark:text-blue-spark-200 dark:ring-blue-spark-300/25',
      },
      {
        variant: 'soft',
        color: 'sky',
        class:
          'bg-sea-sight-100 text-sea-sight-700 ring-sea-sight-700/20 dark:bg-sea-sight-900/40 dark:text-sea-sight-200 dark:ring-sea-sight-300/25',
      },
      {
        variant: 'soft',
        color: 'cyan',
        class:
          'bg-macquarie-100 text-macquarie-700 ring-macquarie-700/20 dark:bg-macquarie-900/40 dark:text-macquarie-200 dark:ring-macquarie-300/25',
      },
      {
        variant: 'soft',
        color: 'teal',
        class:
          'bg-generic-viridian-100 text-generic-viridian-700 ring-generic-viridian-700/20 dark:bg-generic-viridian-900/40 dark:text-generic-viridian-200 dark:ring-generic-viridian-300/25',
      },
      {
        variant: 'soft',
        color: 'green',
        class:
          'bg-green-bell-pepper-100 text-green-bell-pepper-700 ring-green-bell-pepper-700/20 dark:bg-green-bell-pepper-900/40 dark:text-green-bell-pepper-200 dark:ring-green-bell-pepper-300/25',
      },
      {
        variant: 'soft',
        color: 'yellow',
        class:
          'bg-sizzling-sunrise-100 text-sizzling-sunrise-700 ring-sizzling-sunrise-700/20 dark:bg-sizzling-sunrise-900/40 dark:text-sizzling-sunrise-200 dark:ring-sizzling-sunrise-300/25',
      },
      {
        variant: 'soft',
        color: 'amber',
        class:
          'bg-golden-stone-100 text-golden-stone-700 ring-golden-stone-700/20 dark:bg-golden-stone-900/40 dark:text-golden-stone-200 dark:ring-golden-stone-300/25',
      },
      {
        variant: 'soft',
        color: 'orange',
        class:
          'bg-lucky-orange-100 text-lucky-orange-700 ring-lucky-orange-700/20 dark:bg-lucky-orange-900/40 dark:text-lucky-orange-200 dark:ring-lucky-orange-300/25',
      },
      {
        variant: 'soft',
        color: 'red',
        class:
          'bg-electric-crimson-100 text-electric-crimson-700 ring-electric-crimson-700/20 dark:bg-electric-crimson-900/40 dark:text-electric-crimson-200 dark:ring-electric-crimson-300/25',
      },
      {
        variant: 'soft',
        color: 'violet',
        class:
          'bg-snappy-violet-100 text-snappy-violet-700 ring-snappy-violet-700/20 dark:bg-snappy-violet-900/40 dark:text-snappy-violet-200 dark:ring-snappy-violet-300/25',
      },
      {
        variant: 'soft',
        color: 'purple',
        class:
          'bg-psychedelic-purple-100 text-psychedelic-purple-700 ring-psychedelic-purple-700/20 dark:bg-psychedelic-purple-900/40 dark:text-psychedelic-purple-200 dark:ring-psychedelic-purple-300/25',
      },
      {
        variant: 'soft',
        color: 'indigo',
        class:
          'bg-electric-indigo-100 text-electric-indigo-700 ring-electric-indigo-700/20 dark:bg-electric-indigo-900/40 dark:text-electric-indigo-200 dark:ring-electric-indigo-300/25',
      },

      {
        variant: 'solid',
        color: 'primary',
        class: 'bg-primary text-primary-foreground',
      },
      {
        variant: 'solid',
        color: 'neutral',
        class: 'bg-foreground text-background',
      },
      { variant: 'solid', color: 'blue', class: 'bg-blue-spark-700' },
      { variant: 'solid', color: 'sky', class: 'bg-sea-sight-700' },
      { variant: 'solid', color: 'cyan', class: 'bg-macquarie-700' },
      { variant: 'solid', color: 'teal', class: 'bg-generic-viridian-700' },
      { variant: 'solid', color: 'green', class: 'bg-green-bell-pepper-700' },
      { variant: 'solid', color: 'yellow', class: 'bg-sizzling-sunrise-700' },
      { variant: 'solid', color: 'amber', class: 'bg-golden-stone-700' },
      { variant: 'solid', color: 'orange', class: 'bg-lucky-orange-700' },
      { variant: 'solid', color: 'red', class: 'bg-electric-crimson-700' },
      { variant: 'solid', color: 'violet', class: 'bg-snappy-violet-700' },
      { variant: 'solid', color: 'purple', class: 'bg-psychedelic-purple-700' },
      { variant: 'solid', color: 'indigo', class: 'bg-electric-indigo-700' },
    ],
    defaultVariants: {
      variant: 'soft',
      color: 'blue',
      block: false,
    },
  },
);

export type EventChipVariants = VariantProps<typeof eventChipVariants>;
