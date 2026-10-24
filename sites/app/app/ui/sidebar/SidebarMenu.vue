<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import { sidebarMenuVariants } from '.';
import { provideSidebarMenuContext, type SidebarMenuVariant } from './context';

interface Props {
  variant?: SidebarMenuVariant;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
});

provideSidebarMenuContext({
  variant: computed(() => props.variant),
});
</script>

<template>
  <ul :class="cn(sidebarMenuVariants({ variant }), props.class)">
    <slot />
  </ul>
</template>
