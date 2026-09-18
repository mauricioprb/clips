<script setup lang="ts">
import { useId } from 'vue';

defineProps<{
    title: string;
    description?: string;
}>();

const titleId = useId();
</script>

<template>
    <section
        class="grid grid-cols-1 gap-4 px-5 py-6 not-first:border-t not-first:border-(--line) lg:grid-cols-[15rem_minmax(0,1fr)] lg:gap-8"
        :aria-labelledby="titleId"
    >
        <div>
            <h2 :id="titleId" class="text-sm font-semibold">{{ title }}</h2>
            <p v-if="description" class="mt-1 text-sm text-(--muted)">{{ description }}</p>
        </div>
        <div class="grid content-start gap-5">
            <slot />
        </div>
    </section>
</template>
