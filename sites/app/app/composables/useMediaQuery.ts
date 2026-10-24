import { onScopeDispose, readonly, ref, type Ref } from 'vue';

export function useMediaQuery(query: string): Readonly<Ref<boolean>> {
  const matches = ref(false);

  if (typeof window === 'undefined' || typeof window.matchMedia !== 'function') {
    return readonly(matches);
  }

  const mediaQuery = window.matchMedia(query);
  matches.value = mediaQuery.matches;

  const update = (event: MediaQueryListEvent) => {
    matches.value = event.matches;
  };

  mediaQuery.addEventListener('change', update);
  onScopeDispose(() => mediaQuery.removeEventListener('change', update));

  return readonly(matches);
}
