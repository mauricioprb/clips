import { inject, provide, type InjectionKey, type Ref } from 'vue';

export interface NavbarContext {
  variant: Ref<'fixed' | 'floating'>;
}

const NAVBAR_INJECTION_KEY: InjectionKey<NavbarContext> = Symbol('fuse-navbar');

export function provideNavbarContext(context: NavbarContext) {
  provide(NAVBAR_INJECTION_KEY, context);
}

export function useNavbar(): NavbarContext {
  const context = inject(NAVBAR_INJECTION_KEY, null);
  if (!context) {
    throw new Error('useNavbar must be used within a <Navbar>');
  }
  return context;
}
