<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { TabsContent, type TabsContentProps } from 'reka-ui';
import { cn } from '@/lib/utils';

interface Props {
  value: TabsContentProps['value'];
  forceMount?: boolean;
  class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();

defineOptions({ inheritAttrs: false });
</script>

<template>
  <TabsContent
    v-bind="$attrs"
    :value="props.value"
    :force-mount="forceMount"
    :class="
      cn(
        'mt-3 rounded-md focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none',
        props.class,
      )
    "
  >
    <slot />
  </TabsContent>
</template>
