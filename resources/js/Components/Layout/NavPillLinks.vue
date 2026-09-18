<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch, type Component } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    links: { href: string; label: string; shortLabel?: string; icon: Component }[];
    stacked?: boolean;
}>();

const page = usePage();
const list = ref<HTMLElement | null>(null);
const indicator = ref({ left: 0, width: 0, visible: false });

const activeIndex = computed(() =>
    props.links.findIndex(
        (link) =>
            page.url === link.href || page.url.startsWith(`${link.href}/`) || page.url.startsWith(`${link.href}?`),
    ),
);

function measure(): void {
    const item = list.value?.querySelectorAll<HTMLElement>('[data-nav-link]')[activeIndex.value];

    indicator.value = item
        ? { left: item.offsetLeft, width: item.offsetWidth, visible: true }
        : { ...indicator.value, visible: false };
}

watch(activeIndex, () => nextTick(measure));
onMounted(() => {
    measure();
    window.addEventListener('resize', measure);
});
onBeforeUnmount(() => window.removeEventListener('resize', measure));
</script>

<template>
    <ul ref="list" class="relative flex items-center" :class="stacked ? 'justify-around' : 'gap-1'">
        <span
            class="pointer-events-none absolute inset-y-0 left-0 rounded-(--radius-md) bg-(--accent-soft) transition-[transform,width,opacity] duration-300 ease-[cubic-bezier(0.34,1.3,0.64,1)]"
            :class="indicator.visible ? 'opacity-100' : 'opacity-0'"
            :style="{ transform: `translateX(${indicator.left}px)`, width: `${indicator.width}px` }"
            aria-hidden="true"
        />
        <li v-for="(link, index) in links" :key="link.href" :class="stacked ? 'flex-1' : ''">
            <Link
                :href="link.href"
                data-nav-link
                class="relative z-10 flex items-center rounded-(--radius-md) font-medium transition-colors"
                :class="[
                    stacked
                        ? 'flex-col gap-0.5 px-1 py-1.5 text-[0.6875rem] whitespace-nowrap'
                        : 'h-9 gap-2 px-3 text-sm',
                    index === activeIndex ? 'text-(--accent-soft-ink)' : 'text-(--muted) hover:text-(--ink)',
                ]"
                :aria-current="index === activeIndex ? 'page' : undefined"
            >
                <component :is="link.icon" :class="stacked ? 'size-5' : 'size-4'" aria-hidden="true" />
                {{ stacked ? (link.shortLabel ?? link.label) : link.label }}
            </Link>
        </li>
    </ul>
</template>
