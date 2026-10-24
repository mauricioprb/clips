<script setup lang="ts">
import { computed, type HTMLAttributes } from 'vue';
import { ArrowDown } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { tableHeadVariants, type TableHeadVariants } from '.';
import { useFuseLocale } from '@/lib/locale';

interface Props {
  sortable?: boolean;
  sortDirection?: 'asc' | 'desc' | null;
  align?: TableHeadVariants['align'];
  sortLabel?: string;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  align: 'left',
  sortDirection: null,
  sortLabel: () => useFuseLocale().tableHead.sortLabel,
});

const emit = defineEmits<{ sort: [] }>();

const ariaSort = computed(() => {
  if (!props.sortable) return undefined;
  if (props.sortDirection === 'asc') return 'ascending';
  if (props.sortDirection === 'desc') return 'descending';
  return 'none';
});

function handleSort() {
  if (props.sortable) emit('sort');
}
</script>

<template>
  <th scope="col" :aria-sort="ariaSort" :class="cn(tableHeadVariants({ align }), props.class)">
    <component
      :is="sortable ? 'button' : 'div'"
      :type="sortable ? 'button' : undefined"
      :aria-label="sortable ? sortLabel : undefined"
      :class="
        cn(
          'inline-flex items-center gap-1.5',
          align === 'right' && 'flex-row-reverse',
          align === 'center' && 'justify-center',
          sortable &&
            'cursor-pointer rounded-sm transition-colors duration-150 ease-out-quart hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none',
        )
      "
      @click="handleSort"
    >
      <slot />
      <ArrowDown
        v-if="sortable"
        :class="
          cn(
            'size-3.5 shrink-0 transition-transform duration-150 ease-out-quart',
            sortDirection === 'asc' && 'rotate-180',
            sortDirection === null && 'opacity-40',
          )
        "
      />
    </component>
  </th>
</template>
