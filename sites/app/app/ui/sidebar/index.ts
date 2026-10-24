import { cva, type VariantProps } from 'class-variance-authority';

export { default as SidebarProvider } from './SidebarProvider.vue';
export { default as Sidebar } from './Sidebar.vue';
export { default as SidebarHeader } from './SidebarHeader.vue';
export { default as SidebarContent } from './SidebarContent.vue';
export { default as SidebarFooter } from './SidebarFooter.vue';
export { default as SidebarTrigger } from './SidebarTrigger.vue';
export { default as SidebarGroup } from './SidebarGroup.vue';
export { default as SidebarGroupLabel } from './SidebarGroupLabel.vue';
export { default as SidebarMenu } from './SidebarMenu.vue';
export { default as SidebarMenuItem } from './SidebarMenuItem.vue';
export { default as SidebarMenuButton } from './SidebarMenuButton.vue';
export { default as SidebarMenuSub } from './SidebarMenuSub.vue';
export { default as SidebarMenuSubItem } from './SidebarMenuSubItem.vue';
export { default as SidebarMenuSubButton } from './SidebarMenuSubButton.vue';
export { default as SidebarSeparator } from './SidebarSeparator.vue';
export { useSidebar, type SidebarContext } from './context';
export {
  useSidebarMenuContext,
  provideSidebarMenuContext,
  type SidebarMenuVariant,
} from './context';

export const sidebarVariants = cva(
  'group/sidebar flex h-svh w-64 shrink-0 flex-col overflow-hidden bg-background text-foreground transition-[width] duration-200 ease-out-quart data-[collapsed=true]:w-20',
  {
    variants: {
      variant: {
        default: 'border-r border-border',
        floating: 'm-2 h-[calc(100svh-1rem)] rounded-xl border border-border shadow-l',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export type SidebarVariants = VariantProps<typeof sidebarVariants>;

export const sidebarMenuVariants = cva('flex list-none flex-col gap-1', {
  variants: {
    variant: {
      default: '',
      rail: 'border-l border-border/60 group-data-[collapsed=true]/sidebar:border-l-0',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

export type SidebarMenuVariants = VariantProps<typeof sidebarMenuVariants>;

export const sidebarMenuButtonVariants = cva(
  'group/menu-button flex w-full items-center gap-3 text-left font-medium text-muted-foreground outline-none transition-colors duration-150 ease-out-quart cursor-pointer hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:ring-offset-1 focus-visible:ring-offset-background disabled:pointer-events-none disabled:opacity-50 [&_svg]:shrink-0 [&_svg]:text-muted-foreground group-hover/menu-button:[&_svg]:text-foreground group-data-[collapsed=true]/sidebar:mx-auto group-data-[collapsed=true]/sidebar:justify-center group-data-[collapsed=true]/sidebar:px-0 group-data-[collapsed=true]/sidebar:[&>span:last-of-type]:hidden',
  {
    variants: {
      variant: {
        default:
          'rounded-lg px-3 hover:bg-secondary/60 data-[active=true]:bg-secondary data-[active=true]:text-foreground data-[active=true]:shadow-xs data-[active=true]:[&_svg]:text-foreground',
        rail: '-ml-px rounded-none border-l-2 border-transparent pr-3 pl-3.5 hover:border-border data-[active=true]:border-primary-text data-[active=true]:text-primary-text data-[active=true]:[&_svg]:text-primary-text group-data-[collapsed=true]/sidebar:ml-auto group-data-[collapsed=true]/sidebar:rounded-lg group-data-[collapsed=true]/sidebar:border-l-0 group-data-[collapsed=true]/sidebar:pl-0 group-data-[collapsed=true]/sidebar:pr-0',
      },
      size: {
        md: 'h-10 text-action-md [&_svg]:size-5 group-data-[collapsed=true]/sidebar:w-10',
        sm: 'h-9 text-action-md [&_svg]:size-4 group-data-[collapsed=true]/sidebar:w-9',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
    },
  },
);

export type SidebarMenuButtonVariants = VariantProps<typeof sidebarMenuButtonVariants>;

export const sidebarMenuSubButtonVariants = cva(
  'group/menu-sub-button flex h-8 w-full items-center gap-2.5 rounded-md px-2.5 text-left text-action-md font-normal text-muted-foreground outline-none transition-colors duration-150 ease-out-quart cursor-pointer hover:bg-secondary/60 hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:ring-offset-1 focus-visible:ring-offset-background disabled:pointer-events-none disabled:opacity-50 data-[active=true]:bg-secondary data-[active=true]:font-medium data-[active=true]:text-foreground [&_svg]:size-4 [&_svg]:shrink-0 [&_svg]:text-muted-foreground data-[active=true]:[&_svg]:text-foreground group-hover/menu-sub-button:[&_svg]:text-foreground',
);

export type SidebarMenuSubButtonVariants = VariantProps<typeof sidebarMenuSubButtonVariants>;
