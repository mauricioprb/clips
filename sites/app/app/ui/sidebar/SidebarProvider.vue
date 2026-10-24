<script setup lang="ts">
import { onMounted, ref, watch, type HTMLAttributes } from 'vue';
import { Primitive, TooltipProvider, type PrimitiveProps } from 'reka-ui';
import { cn } from '@/lib/utils';
import { useMediaQuery } from '@/composables/useMediaQuery';
import { provideSidebarContext } from './context';

interface Props extends PrimitiveProps {
  storageKey?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  as: 'div',
  storageKey: 'fuse-ui-sidebar',
});

const collapsed = defineModel<boolean>('collapsed', { default: false });

onMounted(() => {
  if (!props.storageKey || typeof window === 'undefined') return;
  const stored = window.localStorage.getItem(props.storageKey);
  if (stored !== null) collapsed.value = stored === 'true';
  watch(collapsed, (value) => {
    window.localStorage.setItem(props.storageKey, String(value));
  });
});

const isMobile = useMediaQuery('(max-width: 1279.98px)');
const openMobile = ref(false);

watch(isMobile, () => {
  openMobile.value = false;
});

function setOpenMobile(value: boolean) {
  openMobile.value = value;
}

provideSidebarContext({
  collapsed,
  isMobile,
  openMobile,
  setOpenMobile,
  toggle: () => {
    if (isMobile.value) {
      openMobile.value = !openMobile.value;
    } else {
      collapsed.value = !collapsed.value;
    }
  },
});
</script>

<template>
  <Primitive :as="as" :as-child="asChild" :class="cn('flex min-h-svh w-full', $props.class)">
    <TooltipProvider :delay-duration="200">
      <slot />
    </TooltipProvider>
  </Primitive>
</template>
