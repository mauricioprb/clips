import { cva } from 'class-variance-authority';

export type { DateValue } from 'reka-ui';

export { default as Calendar } from './Calendar.vue';

export const calendarCellTriggerVariants = cva(
  'group relative inline-flex size-9 items-center justify-center rounded-full text-body-sm tabular-nums text-foreground outline-none transition-colors hover:bg-accent focus-visible:z-10 focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none data-[today]:font-semibold data-[today]:ring-1 data-[today]:ring-inset data-[today]:ring-border data-[outside-view]:text-muted-foreground data-[selected]:bg-primary data-[selected]:font-medium data-[selected]:text-primary-foreground data-[selected]:ring-0 data-[selected]:hover:bg-primary data-[disabled]:pointer-events-none data-[disabled]:text-muted-foreground/40 data-[unavailable]:pointer-events-none data-[unavailable]:text-muted-foreground/40 data-[unavailable]:line-through',
);
