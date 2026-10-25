import { computed, inject, provide, type ComputedRef, type InjectionKey } from 'vue';
import type { ToggleVariants } from '.';

export interface ToggleGroupContext {
  variant: NonNullable<ToggleVariants['variant']>;
  size: NonNullable<ToggleVariants['size']>;
}

const TOGGLE_GROUP_KEY: InjectionKey<ComputedRef<ToggleGroupContext>> = Symbol('fuse-toggle-group');

export function provideToggleGroupContext(ctx: ComputedRef<ToggleGroupContext>) {
  provide(TOGGLE_GROUP_KEY, ctx);
}

export function useToggleGroupContext(): ComputedRef<ToggleGroupContext> {
  return inject(
    TOGGLE_GROUP_KEY,
    computed(() => ({ variant: 'default', size: 'md' })),
  );
}
