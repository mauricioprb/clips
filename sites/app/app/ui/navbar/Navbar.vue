<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { CollapsibleRoot } from 'reka-ui';
import { cn } from '@/lib/utils';
import { navbarVariants, type NavbarVariants } from '.';
import { provideNavbarContext } from './context';

interface Props {
  variant?: NavbarVariants['variant'];
  contained?: boolean;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'fixed',
});

const open = defineModel<boolean>('open', { default: false });

provideNavbarContext({
  variant: computed(() => props.variant ?? 'fixed'),
});
</script>

<template>
  <CollapsibleRoot
    v-model:open="open"
    as="header"
    :data-variant="variant"
    :class="cn(navbarVariants({ variant }), props.class)"
  >
    <div
      :class="
        cn(
          'relative flex h-16 items-center gap-3 px-4 tablet:px-8',
          contained && 'mx-auto max-w-7xl',
        )
      "
    >
      <slot />
    </div>
    <slot name="menu" />
  </CollapsibleRoot>
</template>
