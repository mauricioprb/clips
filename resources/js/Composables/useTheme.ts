import { computed, ref } from 'vue';

const STORAGE_KEY = 'clips-theme';
const isDark = ref(typeof document !== 'undefined' && document.documentElement.classList.contains('dark'));

export function useTheme() {
    function toggleTheme(): void {
        const root = document.documentElement;

        isDark.value = !isDark.value;
        root.classList.add('theme-switching');
        root.classList.toggle('dark', isDark.value);
        localStorage.setItem(STORAGE_KEY, isDark.value ? 'dark' : 'light');

        void window.getComputedStyle(root).opacity;
        requestAnimationFrame(() => root.classList.remove('theme-switching'));
    }

    return { isDark: computed(() => isDark.value), toggleTheme };
}
