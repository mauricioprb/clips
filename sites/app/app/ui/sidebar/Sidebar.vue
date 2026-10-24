<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { Primitive, type PrimitiveProps } from 'reka-ui';
import { cn } from '@/lib/utils';
import { Sheet, SheetContent } from '@/ui/sheet';
import { sidebarVariants, type SidebarVariants } from '.';
import { useSidebar } from './context';
import { useFuseLocale } from '@/lib/locale';

interface Props extends PrimitiveProps {
  variant?: SidebarVariants['variant'];
  mobileLabel?: string;
  label?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  as: 'aside',
  variant: 'default',
  mobileLabel: () => useFuseLocale().sidebar.mobileLabel,
  label: () => useFuseLocale().sidebar.label,
});

const { collapsed, isMobile, openMobile, setOpenMobile } = useSidebar();
</script>

<template>
  <Sheet v-if="isMobile" :open="openMobile" @update:open="setOpenMobile">
    <SheetContent side="left" :title="mobileLabel" class="w-72 max-w-[85vw] gap-0 p-0">
      <aside
        :aria-label="label"
        :data-variant="variant"
        data-collapsed="false"
        class="group/sidebar flex h-full w-full flex-col overflow-hidden bg-background text-foreground **:data-sidebar-trigger:hidden **:data-sidebar-header:pr-12"
      >
        <slot />
      </aside>
    </SheetContent>
  </Sheet>

  <Primitive
    v-else
    :as="as"
    :as-child="asChild"
    :aria-label="label"
    :data-variant="variant"
    :data-collapsed="collapsed"
    :class="cn(sidebarVariants({ variant }), props.class)"
  >
    <slot />
  </Primitive>
</template>
