import { cva } from 'class-variance-authority';

export { SelectRoot, SelectValue, SelectGroup } from 'reka-ui';

export { default as Select } from './Select.vue';
export { default as SelectTrigger } from './SelectTrigger.vue';
export { default as SelectContent } from './SelectContent.vue';
export { default as SelectItem } from './SelectItem.vue';
export { default as SelectLabel } from './SelectLabel.vue';
export { default as SelectSeparator } from './SelectSeparator.vue';

export const selectItemVariants = cva(
  'relative flex cursor-pointer items-center rounded-md py-1.5 pr-2 pl-8 outline-none transition-colors duration-100 select-none data-disabled:pointer-events-none data-disabled:opacity-50 data-highlighted:bg-accent data-highlighted:text-accent-foreground',
);

export interface SelectOption {
  label: string;
  value: string;
  disabled?: boolean;
}
