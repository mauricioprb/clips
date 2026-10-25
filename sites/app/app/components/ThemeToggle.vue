<script setup lang="ts">
import { Monitor, Moon, Sun } from '@lucide/vue';
import { useColorMode } from '~/composables/useColorMode';
import { ToggleGroup, ToggleGroupItem } from '~/ui/toggle';

const { theme, setTheme } = useColorMode();
const options = [
  { value: 'light', label: 'Tema claro', icon: Sun },
  { value: 'dark', label: 'Tema escuro', icon: Moon },
  { value: 'system', label: 'Tema do sistema', icon: Monitor },
] as const;

function select(value: unknown) {
  if (value === 'light' || value === 'dark' || value === 'system') setTheme(value);
}
</script>

<template>
  <ToggleGroup
    type="single"
    variant="segmented"
    size="sm"
    aria-label="Aparência"
    class="gap-0.5 p-0.5"
    :model-value="theme"
    @update:model-value="select"
  >
    <ToggleGroupItem
      v-for="option in options"
      :key="option.value"
      :value="option.value"
      :aria-label="option.label"
      :title="option.label"
      class="h-7 min-w-7 px-1.5 [&_svg]:size-3.5"
    >
      <component :is="option.icon" />
    </ToggleGroupItem>
  </ToggleGroup>
</template>
