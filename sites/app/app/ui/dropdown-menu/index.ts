import { cva, type VariantProps } from 'class-variance-authority';

export {
  DropdownMenuRoot as DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuGroup,
  DropdownMenuRadioGroup,
  DropdownMenuSub,
} from 'reka-ui';

export { default as DropdownMenuContent } from './DropdownMenuContent.vue';
export { default as DropdownMenuItem } from './DropdownMenuItem.vue';
export { default as DropdownMenuCheckboxItem } from './DropdownMenuCheckboxItem.vue';
export { default as DropdownMenuRadioItem } from './DropdownMenuRadioItem.vue';
export { default as DropdownMenuLabel } from './DropdownMenuLabel.vue';
export { default as DropdownMenuSeparator } from './DropdownMenuSeparator.vue';
export { default as DropdownMenuShortcut } from './DropdownMenuShortcut.vue';
export { default as DropdownMenuSubTrigger } from './DropdownMenuSubTrigger.vue';
export { default as DropdownMenuSubContent } from './DropdownMenuSubContent.vue';

export const dropdownMenuItemVariants = cva(
  'relative flex w-full cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-body-sm outline-none transition-colors duration-100 select-none data-disabled:pointer-events-none data-disabled:opacity-50 data-highlighted:bg-accent data-highlighted:text-accent-foreground [&_svg]:size-4 [&_svg]:shrink-0 [&_svg]:text-muted-foreground data-highlighted:[&_svg]:text-accent-foreground',
  {
    variants: {
      inset: {
        true: 'pl-8',
      },
      variant: {
        default: '',
        danger:
          'text-danger [&_svg]:text-danger data-highlighted:bg-danger-soft data-highlighted:text-danger-soft-foreground data-highlighted:[&_svg]:text-danger-soft-foreground',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export type DropdownMenuItemVariants = VariantProps<typeof dropdownMenuItemVariants>;
