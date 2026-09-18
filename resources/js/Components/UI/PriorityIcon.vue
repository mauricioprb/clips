<script setup lang="ts">
import { computed } from 'vue';
import type { Priority } from '@/types/models';

const props = defineProps<{ priority: Priority }>();

const filled = computed(() => ({ high: 3, medium: 2, low: 1 })[props.priority]);
</script>

<template>
    <span class="inline-flex size-4 shrink-0 items-end justify-center gap-0.5 pb-0.5" aria-hidden="true">
        <span
            v-for="(height, index) in ['h-1.5', 'h-2.5', 'h-3.5']"
            :key="height"
            class="w-[3px] rounded-[1px]"
            :class="[height, index < filled ? 'bg-(--accent)' : 'bg-(--line-strong)']"
        />
    </span>
</template>
