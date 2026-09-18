<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { CalendarOff, GraduationCap, KeyRound } from '@lucide/vue';
import PageHeader from '@/Components/UI/PageHeader.vue';
import { useI18n } from '@/Composables/useI18n';

const { t } = useI18n();
const page = usePage();

const tabs = computed(() => [
    { href: '/configuracoes/perfil', label: t('settings.tab.profile'), icon: GraduationCap },
    { href: '/configuracoes/feriados', label: t('settings.tab.holidays'), icon: CalendarOff },
    { href: '/configuracoes/seguranca', label: t('settings.tab.security'), icon: KeyRound },
]);

function isActive(href: string): boolean {
    return page.url.startsWith(href);
}
</script>

<template>
    <div>
        <PageHeader :title="t('settings.title')" />
        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-[13rem_minmax(0,1fr)] md:gap-10">
            <nav :aria-label="t('settings.tabs')">
                <ul class="flex gap-1 overflow-x-auto pb-1 md:flex-col md:overflow-visible md:pb-0">
                    <li v-for="tab in tabs" :key="tab.href" class="shrink-0">
                        <Link
                            :href="tab.href"
                            class="flex items-center gap-2.5 rounded-(--radius-md) px-3 py-2 text-sm font-medium whitespace-nowrap transition-colors"
                            :class="
                                isActive(tab.href)
                                    ? 'bg-(--accent-soft) text-(--accent-soft-ink)'
                                    : 'text-(--muted) hover:bg-(--surface-sunken) hover:text-(--ink)'
                            "
                            :aria-current="isActive(tab.href) ? 'page' : undefined"
                        >
                            <component :is="tab.icon" class="hidden size-4 shrink-0 md:block" aria-hidden="true" />
                            {{ tab.label }}
                        </Link>
                    </li>
                </ul>
            </nav>
            <div class="min-w-0">
                <slot />
            </div>
        </div>
    </div>
</template>
