<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { TabsList } from 'reka-ui';
import { cn } from '@/lib/utils';
import { tabsListVariants } from '.';
import { provideTabsContext, type TabsSize, type TabsVariant } from './context';

interface Props {
  variant?: TabsVariant;
  size?: TabsSize;
  loop?: boolean;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'underline',
  size: 'md',
  loop: true,
});

defineOptions({ inheritAttrs: false });

provideTabsContext({
  variant: computed(() => props.variant),
  size: computed(() => props.size),
});
</script>

<template>
  <TabsList v-bind="$attrs" :loop="loop" :class="cn(tabsListVariants({ variant }), props.class)">
    <slot />
  </TabsList>
</template>
