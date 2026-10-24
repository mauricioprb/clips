import { cva } from 'class-variance-authority';

export { ComboboxRoot, ComboboxGroup, ComboboxLabel, ComboboxSeparator } from 'reka-ui';

export { default as Combobox } from './Combobox.vue';
export { default as ComboboxAnchor } from './ComboboxAnchor.vue';
export { default as ComboboxInput } from './ComboboxInput.vue';
export { default as ComboboxTrigger } from './ComboboxTrigger.vue';
export { default as ComboboxContent } from './ComboboxContent.vue';
export { default as ComboboxItem } from './ComboboxItem.vue';
export { default as ComboboxEmpty } from './ComboboxEmpty.vue';

export const comboboxItemVariants = cva(
  'relative flex cursor-pointer items-center rounded-md py-1.5 pr-2 pl-8 outline-none transition-colors duration-100 select-none data-disabled:pointer-events-none data-disabled:opacity-50 data-highlighted:bg-accent data-highlighted:text-accent-foreground',
);

export interface ComboboxOption {
  label: string;
  value: string;
  disabled?: boolean;
}
