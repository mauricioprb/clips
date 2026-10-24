import { computed, inject, provide, type ComputedRef, type InjectionKey, type Ref } from 'vue';
import type { SidebarMenuButtonVariants } from '.';

export interface SidebarContext {
  collapsed: Ref<boolean>;
  isMobile: Ref<boolean>;
  openMobile: Ref<boolean>;
  setOpenMobile: (value: boolean) => void;
  toggle: () => void;
}

export type SidebarMenuVariant = NonNullable<SidebarMenuButtonVariants['variant']>;

interface SidebarMenuContext {
  variant: ComputedRef<SidebarMenuVariant>;
}

const SIDEBAR_MENU_INJECTION_KEY: InjectionKey<SidebarMenuContext> = Symbol('fuse-sidebar-menu');

export function provideSidebarMenuContext(context: SidebarMenuContext) {
  provide(SIDEBAR_MENU_INJECTION_KEY, context);
}

export function useSidebarMenuContext(): SidebarMenuContext {
  return inject(SIDEBAR_MENU_INJECTION_KEY, {
    variant: computed<SidebarMenuVariant>(() => 'default'),
  });
}

const SIDEBAR_INJECTION_KEY: InjectionKey<SidebarContext> = Symbol('fuse-sidebar');

export function provideSidebarContext(context: SidebarContext) {
  provide(SIDEBAR_INJECTION_KEY, context);
}

export function useSidebar(): SidebarContext {
  const context = inject(SIDEBAR_INJECTION_KEY, null);
  if (!context) {
    throw new Error('useSidebar must be used within a <SidebarProvider>');
  }
  return context;
}
