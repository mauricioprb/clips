import { cva, type VariantProps } from 'class-variance-authority';

export { default as Table } from './Table.vue';
export { default as TableHeader } from './TableHeader.vue';
export { default as TableBody } from './TableBody.vue';
export { default as TableFooter } from './TableFooter.vue';
export { default as TableRow } from './TableRow.vue';
export { default as TableHead } from './TableHead.vue';
export { default as TableCell } from './TableCell.vue';
export { default as TableCaption } from './TableCaption.vue';

export const tableHeadVariants = cva(
  'h-11 px-4 align-middle text-body-sm font-medium whitespace-nowrap text-muted-foreground',
  {
    variants: {
      align: {
        left: 'text-left',
        center: 'text-center',
        right: 'text-right',
      },
    },
    defaultVariants: {
      align: 'left',
    },
  },
);

export type TableHeadVariants = VariantProps<typeof tableHeadVariants>;

export const tableCellVariants = cva('px-4 py-3 align-middle text-foreground', {
  variants: {
    align: {
      left: 'text-left',
      center: 'text-center',
      right: 'text-right',
    },
  },
  defaultVariants: {
    align: 'left',
  },
});

export type TableCellVariants = VariantProps<typeof tableCellVariants>;
