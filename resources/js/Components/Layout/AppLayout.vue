<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { CalendarDays, CalendarRange, ListChecks, LogOut, Moon, Settings, Sun, Users } from '@lucide/vue';
import ConfirmDialog from 'primevue/confirmdialog';
import NavPillLinks from '@/Components/Layout/NavPillLinks.vue';
import BrandLogo from '@/Components/UI/BrandLogo.vue';
import StatusToast from '@/Components/UI/StatusToast.vue';
import { useI18n } from '@/Composables/useI18n';
import { useTheme } from '@/Composables/useTheme';

const { t } = useI18n();
const { isDark, toggleTheme } = useTheme();
const page = usePage();

const links = computed(() => [
    { href: '/mes', label: t('nav.month'), icon: CalendarDays },
    { href: '/grade-semanal', label: t('nav.schedule'), shortLabel: t('nav.scheduleShort'), icon: CalendarRange },
    { href: '/atividades-padrao', label: t('nav.activities'), icon: ListChecks },
    ...(page.props.auth.user?.isAdmin ? [{ href: '/usuarios', label: t('nav.users'), icon: Users }] : []),
    { href: '/configuracoes', label: t('nav.settings'), shortLabel: t('nav.settingsShort'), icon: Settings },
]);

const themeLabel = computed(() => (isDark.value ? t('theme.useLight') : t('theme.useDark')));
const scrolled = ref(false);

function updateScrolled(): void {
    scrolled.value = window.scrollY > 4;
}

onMounted(() => {
    updateScrolled();
    window.addEventListener('scroll', updateScrolled, { passive: true });
});
onBeforeUnmount(() => window.removeEventListener('scroll', updateScrolled));

const iconButton =
    'grid size-9 place-items-center rounded-(--radius-md) text-(--muted) transition-colors hover:bg-(--surface-sunken) hover:text-(--ink)';
</script>

<template>
    <div class="min-h-dvh">
        <a
            href="#main"
            class="fixed top-2 left-2 z-50 -translate-y-16 rounded-(--radius-md) bg-(--accent) px-4 py-2 text-sm font-semibold text-(--accent-ink) focus-visible:translate-y-0 motion-reduce:transition-none"
        >
            {{ t('nav.skip') }}
        </a>

        <header
            class="sticky top-0 z-40 border-b transition-colors"
            :class="
                scrolled ? 'border-(--line) bg-(--surface)/85 backdrop-blur-md' : 'border-transparent bg-(--surface)'
            "
        >
            <div class="mx-auto grid h-20 max-w-7xl grid-cols-[1fr_auto_1fr] items-center gap-4 px-4 sm:px-6">
                <Link href="/mes" class="h-6 justify-self-start rounded-sm">
                    <BrandLogo class="h-6" />
                </Link>

                <nav :aria-label="t('nav.label')" class="hidden md:block">
                    <NavPillLinks :links="links" />
                </nav>

                <div class="col-start-3 flex items-center gap-1 justify-self-end">
                    <button
                        type="button"
                        :class="iconButton"
                        :aria-label="themeLabel"
                        :title="themeLabel"
                        @click="toggleTheme"
                    >
                        <Sun v-if="isDark" class="size-4" aria-hidden="true" />
                        <Moon v-else class="size-4" aria-hidden="true" />
                    </button>
                    <button
                        type="button"
                        :class="[iconButton, 'hover:bg-(--danger)/10 hover:text-(--danger)']"
                        :aria-label="t('nav.logout')"
                        :title="t('nav.logout')"
                        @click="router.post('/sair')"
                    >
                        <LogOut class="size-4" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </header>

        <main id="main" tabindex="-1" class="mx-auto max-w-7xl px-4 pt-6 pb-28 outline-none sm:px-6 md:pt-10 md:pb-12">
            <slot />
        </main>

        <nav
            :aria-label="t('nav.label')"
            class="fixed inset-x-0 bottom-0 z-40 border-t border-(--line) bg-(--surface-raised) px-2 pt-1.5 pb-[calc(0.375rem+env(safe-area-inset-bottom))] md:hidden"
        >
            <NavPillLinks :links="links" stacked />
        </nav>

        <StatusToast />
        <ConfirmDialog class="w-[min(100%-2rem,28rem)]" />
    </div>
</template>
