<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import { tableCellVariants, type TableCellVariants } from '.';

interface Props {
  align?: TableCellVariants['align'];
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  align: 'left',
});
</script>

<template>
  <td :class="cn(tableCellVariants({ align }), props.class)">
    <slot />
  </td>
</template>
