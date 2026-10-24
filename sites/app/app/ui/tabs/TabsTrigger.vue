<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { TabsTrigger, type TabsTriggerProps } from 'reka-ui';
import { cn } from '@/lib/utils';
import { tabsTriggerVariants } from '.';
import { useTabsContext, type TabsSize, type TabsVariant } from './context';

interface Props extends /* @vue-ignore */ TabsTriggerProps {
  value: TabsTriggerProps['value'];
  disabled?: boolean;
  variant?: TabsVariant;
  size?: TabsSize;
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();

defineOptions({ inheritAttrs: false });

const context = useTabsContext();
const variant = computed(() => props.variant ?? context.variant.value);
const size = computed(() => props.size ?? context.size.value);
</script>

<template>
  <TabsTrigger
    v-bind="$attrs"
    :value="props.value"
    :disabled="props.disabled"
    :class="cn(tabsTriggerVariants({ variant, size }), props.class)"
  >
    <slot />
  </TabsTrigger>
</template>
