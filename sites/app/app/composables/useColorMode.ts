import { computed, effectScope, onMounted, ref, watch } from 'vue';

export type Theme = 'light' | 'dark' | 'system';

const STORAGE_KEY = 'fuse-ui-theme';
const theme = ref<Theme>('system');
const systemDark = ref(false);
const mounted = ref(false);
let initialized = false;

function resolveDark(value: Theme) {
  return value === 'dark' || (value === 'system' && systemDark.value);
}

function applyTheme(value: Theme) {
  if (typeof document === 'undefined') return;

  const html = document.documentElement;
  html.classList.add('disable-transitions');

  html.classList.toggle('dark', resolveDark(value));

  void html.offsetHeight;
  html.classList.remove('disable-transitions');
}

function initialize() {
  if (initialized || typeof window === 'undefined') return;
  initialized = true;

  const query = window.matchMedia('(prefers-color-scheme: dark)');
  systemDark.value = query.matches;

  const stored = localStorage.getItem(STORAGE_KEY) as Theme | null;
  theme.value = stored ?? 'system';
  applyTheme(theme.value);

  query.addEventListener('change', (event) => {
    systemDark.value = event.matches;
    if (theme.value === 'system') applyTheme('system');
  });

  effectScope(true).run(() => {
    watch(theme, (value) => {
      localStorage.setItem(STORAGE_KEY, value);
      applyTheme(value);
    });
  });
}

export function useColorMode() {
  initialize();

  onMounted(() => {
    mounted.value = true;
  });

  const isDark = computed(() => (mounted.value ? resolveDark(theme.value) : false));

  function setTheme(value: Theme) {
    theme.value = value;
  }

  function toggle() {
    theme.value = isDark.value ? 'light' : 'dark';
  }

  return { theme, isDark, setTheme, toggle };
}
