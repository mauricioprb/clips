import { computed, inject, provide, type ComputedRef, type InjectionKey } from 'vue';
import type { TabsTriggerVariants } from '.';

export type TabsVariant = NonNullable<TabsTriggerVariants['variant']>;
export type TabsSize = NonNullable<TabsTriggerVariants['size']>;

interface TabsContext {
  variant: ComputedRef<TabsVariant>;
  size: ComputedRef<TabsSize>;
}

const TABS_KEY: InjectionKey<TabsContext> = Symbol('tabs');

export function provideTabsContext(context: TabsContext) {
  provide(TABS_KEY, context);
}

export function useTabsContext(): TabsContext {
  return inject(TABS_KEY, {
    variant: computed<TabsVariant>(() => 'underline'),
    size: computed<TabsSize>(() => 'md'),
  });
}
