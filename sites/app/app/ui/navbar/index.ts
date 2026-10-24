import { cva, type VariantProps } from 'class-variance-authority';

export { default as Navbar } from './Navbar.vue';
export { default as NavbarBrand } from './NavbarBrand.vue';
export { default as NavbarNav } from './NavbarNav.vue';
export { default as NavbarLink } from './NavbarLink.vue';
export { default as NavbarActions } from './NavbarActions.vue';
export { default as NavbarIconButton } from './NavbarIconButton.vue';
export { default as NavbarTrigger } from './NavbarTrigger.vue';
export { default as NavbarMobileMenu } from './NavbarMobileMenu.vue';
export { default as NavbarMobileLink } from './NavbarMobileLink.vue';
export { default as NavbarMobileSub } from './NavbarMobileSub.vue';
export { useNavbar, type NavbarContext } from './context';

export const navbarVariants = cva('group/navbar relative block bg-background text-foreground', {
  variants: {
    variant: {
      fixed: 'w-full border-b border-border',
      floating: 'rounded-2xl border border-border shadow-l',
    },
  },
  defaultVariants: {
    variant: 'fixed',
  },
});

export type NavbarVariants = VariantProps<typeof navbarVariants>;

export const navbarLinkVariants = cva(
  'relative inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-action-md font-medium text-muted-foreground outline-none transition-colors duration-150 ease-out-quart cursor-pointer hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:ring-offset-1 focus-visible:ring-offset-background data-[active=true]:text-primary-text',
);

export type NavbarLinkVariants = VariantProps<typeof navbarLinkVariants>;

export const navbarIconButtonVariants = cva(
  'inline-flex size-9 shrink-0 items-center justify-center rounded-lg text-muted-foreground outline-none transition-colors duration-150 ease-out-quart cursor-pointer hover:bg-secondary hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:ring-offset-1 focus-visible:ring-offset-background disabled:pointer-events-none disabled:opacity-50 [&_svg]:size-5',
);

export type NavbarIconButtonVariants = VariantProps<typeof navbarIconButtonVariants>;

export const navbarMobileLinkVariants = cva(
  'group/navmobile flex h-11 w-full items-center gap-3 rounded-lg px-3 text-left text-action-md font-medium text-muted-foreground outline-none transition-colors duration-150 ease-out-quart cursor-pointer hover:bg-secondary/60 hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:ring-offset-1 focus-visible:ring-offset-background disabled:pointer-events-none disabled:opacity-50 data-[active=true]:bg-secondary data-[active=true]:text-foreground [&_svg]:size-5 [&_svg]:shrink-0 [&_svg]:text-muted-foreground data-[active=true]:[&_svg]:text-foreground group-hover/navmobile:[&_svg]:text-foreground',
);

export type NavbarMobileLinkVariants = VariantProps<typeof navbarMobileLinkVariants>;
