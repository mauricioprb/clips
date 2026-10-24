import { cva, type VariantProps } from 'class-variance-authority';

export {
  DialogRoot as Sheet,
  DialogTrigger as SheetTrigger,
  DialogClose as SheetClose,
} from 'reka-ui';

export { default as SheetContent } from './SheetContent.vue';
export { default as SheetHeader } from './SheetHeader.vue';
export { default as SheetFooter } from './SheetFooter.vue';
export { default as SheetTitle } from './SheetTitle.vue';
export { default as SheetDescription } from './SheetDescription.vue';

export const sheetVariants = cva(
  'fixed z-50 flex flex-col gap-4 border-border bg-popover p-5 text-popover-foreground shadow-xl',
  {
    variants: {
      side: {
        left: 'inset-y-0 left-0 h-full w-3/4 max-w-sm border-r data-[state=open]:animate-sheet-in-left data-[state=closed]:animate-sheet-out-left',
        right:
          'inset-y-0 right-0 h-full w-3/4 max-w-sm border-l data-[state=open]:animate-sheet-in-right data-[state=closed]:animate-sheet-out-right',
        top: 'inset-x-0 top-0 max-h-[85svh] w-full border-b data-[state=open]:animate-sheet-in-top data-[state=closed]:animate-sheet-out-top',
        bottom:
          'inset-x-0 bottom-0 max-h-[85svh] w-full rounded-t-xl border-t data-[state=open]:animate-sheet-in-bottom data-[state=closed]:animate-sheet-out-bottom',
      },
    },
    defaultVariants: {
      side: 'right',
    },
  },
);

export type SheetVariants = VariantProps<typeof sheetVariants>;
