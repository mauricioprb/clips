<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, type HTMLAttributes } from 'vue';
import { Search } from '@lucide/vue';
import { cn } from '@/lib/utils';
import { Kbd } from '@/ui/kbd';
import { useFuseLocale } from '@/lib/locale';

interface Props {
  label?: string;
  shortcut?: string | false;
  class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
  label: () => useFuseLocale().appShellSearch.label,
  shortcut: 'k',
});

const button = ref<HTMLButtonElement>();
const modifier = ref('⌘');

function onKeydown(event: KeyboardEvent) {
  if (!props.shortcut) return;
  if (!(event.metaKey || event.ctrlKey)) return;
  if (event.key.toLowerCase() !== props.shortcut.toLowerCase()) return;
  event.preventDefault();
  button.value?.click();
}

onMounted(() => {
  if (!/mac|iphone|ipad/i.test(navigator.userAgent)) modifier.value = 'Ctrl';
  window.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));
</script>

<template>
  <button
    ref="button"
    type="button"
    :aria-keyshortcuts="
      shortcut ? `Meta+${shortcut.toUpperCase()} Control+${shortcut.toUpperCase()}` : undefined
    "
    :class="
      cn(
        'flex h-9 w-full max-w-sm min-w-0 cursor-pointer items-center gap-2 rounded-lg border border-input bg-background px-3 text-action-md text-muted-foreground shadow-xs transition-[color,border-color,box-shadow] duration-150 ease-out-quart outline-none hover:border-foreground/30 hover:text-foreground focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/30 max-tablet:size-9 max-tablet:justify-center max-tablet:px-0',
        props.class,
      )
    "
  >
    <Search class="size-4 shrink-0" />
    <span class="flex-1 truncate text-left max-tablet:sr-only">
      {{ label }}
    </span>
    <span v-if="shortcut" class="flex items-center gap-1 max-tablet:hidden">
      <Kbd size="sm">{{ modifier }}</Kbd>
      <Kbd size="sm">{{ shortcut.toUpperCase() }}</Kbd>
    </span>
  </button>
</template>
